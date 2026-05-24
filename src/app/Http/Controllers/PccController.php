<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;      // 用于读取 Excel 文件
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;     // 用于生成 Excel 文件
use Illuminate\Support\Str;                    // 用于生成 UUID（唯一标识）
use Illuminate\Support\Facades\Http;           // Laravel HTTP 客户端，用于调用外部 API

/**
 * PCC 通关编码校验控制器
 * 
 * 功能：上传 Excel → 逐条调用韩国海关 API 校验 PCC → 返回结果
 */
class PccController extends Controller
{
    /**
     * 显示 PCC 校验工具的页面
     * 对应 GET /tools/pcc
     */
    public function index()
    {
        // 渲染视图，视图文件在 resources/views/tools/pcc.blade.php
        return view('tools.pcc');
    }

    /**
     * 接收上传的 Excel 文件，启动后台异步校验任务
     * 对应 POST /tools/pcc/validate
     */
    public function validate(Request $request)
    {
        // ===== 1. 校验上传的文件 =====
        // validate() 是 Laravel 自带的表单验证方法
        // required: 必填 | file: 必须是文件 | mimes: 只允许 xlsx/xls | max: 最大 10MB
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        // ===== 2. 读取 Excel 内容 =====
        $file = $request->file('file');                          // 获取上传的文件
        $spreadsheet = IOFactory::load($file->getPathname());    // 用 PhpSpreadsheet 加载 Excel
        $worksheet = $spreadsheet->getActiveSheet();             // 获取当前工作表
        $rows = $worksheet->toArray();                           // 把整个表转成二维数组

        // ===== 3. 跳过表头行，提取有效数据 =====
        // array_slice($rows, 1) 表示从第2行开始取（跳过第1行表头）
        $dataRows = [];
        foreach (array_slice($rows, 1) as $row) {
            // 如果 PCC / 韩文名 / 电话 / 邮编 任一列有值，就当作有效数据
            if (!empty($row[0] ?? '') || !empty($row[1] ?? '') || !empty($row[2] ?? '') || !empty($row[3] ?? '')) {
                $dataRows[] = $row;
            }
        }

        // 如果全是空行，返回错误
        if (empty($dataRows)) {
            return response()->json(['error' => 'Excel 中没有有效数据'], 400);
        }

        // ===== 4. 限制最大处理条数 =====
        $maxRows = 500;
        $dataRows = array_slice($dataRows, 0, $maxRows);

        // ===== 5. 创建任务记录（存入缓存） =====
        // Str::uuid() 生成一个唯一 ID，用来标识这次校验任务
        $taskId = (string) Str::uuid();
        $total = count($dataRows);

        // cache()->put() 把数据存到 Laravel 缓存中
        // 默认存在文件或数据库里，这里用默认的 file 缓存
        // 第三个参数 3600 表示缓存过期时间（秒），即1小时后自动删除
        cache()->put("pcc_task_{$taskId}", [
            'complete'    => false,    // 是否完成
            'total'       => $total,   // 总条数
            'done'        => 0,        // 已处理条数
            'pass'        => 0,        // 通过条数
            'fail'        => 0,        // 失败条数
            'details'          => [],       // 每条数据的校验结果
            'download_id'      => null,     // 通过数据 Excel 下载 ID
            'download_id_fail' => null,     // 失败数据 Excel 下载 ID
        ], 3600);

        // ===== 6. 后台异步执行校验 =====
        // dispatch(...)->afterResponse() 的意思：
        // "先把 200 响应返回给前端，再在后台慢慢处理"
        // 这样前端不用干等，可以通过轮询获取进度
        dispatch(function () use ($taskId, $dataRows) {
            $this->processTask($taskId, $dataRows);
        })->afterResponse();

        // 记录查询日志
        QueryLog::create(['user_id' => auth()->id(), 'tool' => 'pcc', 'query_key' => $request->file('file')->getClientOriginalName(), 'status' => 'success', 'result_count' => $total]);

        // ===== 7. 立刻返回 task_id 给前端 =====
        // 前端拿着这个 task_id，通过 /tools/pcc/status/{taskId} 轮询进度
        return response()->json([
            'task_id' => $taskId,
            'total'   => $total,
        ]);
    }

    /**
     * 查询校验任务的当前进度
     * 前端每隔 1.5 秒调用一次这个接口
     * 对应 GET /tools/pcc/status/{taskId}
     */
    public function status($taskId)
    {
        // 从缓存中读取任务数据
        $task = cache()->get("pcc_task_{$taskId}");

        // 如果缓存中找不到，说明任务已过期（超过1小时）或不存在
        if (!$task) {
            return response()->json(['error' => '任务不存在或已过期'], 404);
        }

        // 返回任务状态（complete/done/pass/fail/details 等）
        return response()->json($task);
    }

    /**
     * 下载校验通过的数据（Excel 文件）
     * 对应 GET /tools/pcc/download/{downloadId}
     */
    public function download($downloadId)
    {
        // storage_path() 获取 storage 目录的绝对路径
        // 文件存在 storage/app/pcc_results/ 目录下
        $path = storage_path("app/pcc_results/{$downloadId}.xlsx");

        if (!file_exists($path)) {
            abort(404, '文件不存在或已过期');
        }

        // response()->download() 是 Laravel 提供的文件下载方法
        return response()->download($path, 'PCC校验通过数据.xlsx');
    }

    /**
     * 下载校验失败的数据（Excel 文件，多一列"失败原因"）
     * 对应 GET /tools/pcc/download-fail/{downloadId}
     */
    public function downloadFail($downloadId)
    {
        $path = storage_path("app/pcc_results/{$downloadId}.xlsx");

        if (!file_exists($path)) {
            abort(404, '文件不存在或已过期');
        }

        return response()->download($path, 'PCC校验失败数据.xlsx');
    }

    // ========================================================================
    // 以下是私有方法，不对外暴露路由
    // private 表示只能在类内部调用
    // ========================================================================

    /**
     * 手动单个校验：输入 PCC/姓名/电话/邮编，实时查看 API 原始返回
     */
    public function checkSingle(Request $request)
    {
        $request->validate([
            'pcc'   => 'required|string|max:20',
            'name'  => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'zip'   => 'required|string|max:10',
        ]);

        $pcc  = trim($request->input('pcc'));
        $name = trim($request->input('name'));
        $phone = trim($request->input('phone'));
        $zip  = trim($request->input('zip'));

        if (!preg_match('/^P[A-Z0-9]{12}$/i', $pcc)) {
            return response()->json(['success' => false, 'message' => 'PCC 格式错误：需以P开头且13位']);
        }
        $cleanPhone = preg_replace('/[\s-]/', '', $phone);
        if (!preg_match('/^\d{10,11}$/', $cleanPhone)) {
            return response()->json(['success' => false, 'message' => '电话格式错误']);
        }

        try {
            // 构建请求参数（匹配 verify_pcc.py 脚本）
            $params = http_build_query([
                'crkyCn'    => 'e220w270s066w370y070x050d0',
                'persEcm'   => $pcc,
                'pltxNm'    => $name,
                'cralTelno' => $cleanPhone,
                'custPsno'  => $zip,
            ]);
            $queryUrl = 'https://unipass.customs.go.kr:38010/ext/rest/persEcmQry/retrievePersEcm?' . $params;

            // 使用 file_get_contents + stream context
            $body = false;
            for ($attempt = 0; $attempt <= 2; $attempt++) {
                if ($attempt > 0) usleep(1000000 * $attempt);

                $ctx = stream_context_create(['ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name'  => false,
                ], 'http' => [
                    'timeout' => 15,
                    'user_agent' => 'Mozilla/5.0',
                ]]);

                try {
                    $body = @file_get_contents($queryUrl, false, $ctx);
                    if ($body !== false) break;
                } catch (\Exception $e) {
                    $body = false;
                }
            }

            if ($body === false) {
                return response()->json(['success' => false, 'message' => 'API 请求失败：请联系管理员检查网络', 'raw' => null]);
            }

            $xml = simplexml_load_string($body);
            $tCnt = $xml ? (string)$xml->tCnt : '0';

            // 提取并翻译错误信息
            $errMsg = '';
            // ntceInfo 是常见错误消息节点，persEcmQryRtnErrInfoVo->errMsgCn 是另一格式
            $rawErr = '';
            if ($xml && isset($xml->ntceInfo)) {
                $rawErr = (string)$xml->ntceInfo;
            } elseif ($xml && isset($xml->persEcmQryRtnErrInfoVo->errMsgCn)) {
                $rawErr = (string)$xml->persEcmQryRtnErrInfoVo->errMsgCn;
            }
            if ($rawErr) {
                $errMsg = $this->translatePccError($rawErr);
            }

            return response()->json([
                'success' => $tCnt === '1',
                'message' => $tCnt === '1' ? '✅ 验证通过' : '❌ ' . ($errMsg ?: '姓名/电话/邮编不匹配'),
                'tCnt'    => $tCnt,
                'raw'     => $body,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '异常：' . $e->getMessage(), 'raw' => null]);
        }
    }

    /**
     * 翻译韩国海关 API 返回的韩文错误信息
     */
    private function translatePccError(string $ko): string
    {
        $trimmed = rtrim($ko, '. ');
        $map = [
            '존재하지 않는 인증키입니다' => 'API 认证密钥无效',
            '존재하지 않은 인증키입니다' => 'API 认证密钥无效',
            '유효하지 않은 인증키입니다' => 'API 认证密钥已失效',
            '권한이 없습니다' => '无权限访问',
            '조회된 결과가 없습니다' => '未查询到结果',
            '필수 입력값이 누락되었습니다' => '必填参数缺失',
            '데이터를 찾을 수 없습니다' => '未找到数据',
            '정보 불일치' => '信息不匹配',
        ];
        return $map[$trimmed] ?? $ko;
    }

    /**
     * 核心处理逻辑：逐条校验 PCC 编码
     * 
     * @param string $taskId   任务 ID
     * @param array  $dataRows 从 Excel 读取的数据（二维数组）
     */
    private function processTask($taskId, $dataRows)
    {
        $passRows = [];   // 校验通过的记录，用于生成通过 Excel
        $failRows = [];   // 校验失败的记录（含失败原因），用于生成失败 Excel
        $details = [];    // 每条数据的校验详情（含失败原因）
        $done = 0;        // 已处理计数
        $total = count($dataRows);

        // ===== 逐条处理 =====
        // 用 foreach 循环遍历每一行数据
        foreach ($dataRows as $i => $row) {
            // trim() 去掉字符串两头的空格
            $pcc     = trim($row[0] ?? '');   // 第1列：通关编码(PCC)
            $name    = trim($row[1] ?? '');    // 第2列：韩文名
            $phone   = trim($row[2] ?? '');    // 第3列：电话
            $zipcode = trim($row[3] ?? '');    // 第4列：邮编
            $ename   = trim($row[4] ?? '');    // 第5列：英文名（选填）

            // ---------- 格式校验（不调用 API，直接拒绝） ----------

            // 检查 PCC 是否为空
            if (empty($pcc)) {
                $details[] = ['row' => $i + 2, 'pcc' => '', 'hname' => $name, 'status' => 'fail', 'msg' => '通关编码为空'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            // 检查 PCC 格式：必须以 P 开头，共13位字母或数字
            // preg_match() 是 PHP 的正则匹配函数
            // /^P[A-Z0-9]{12}$/i 含义：
            //   ^P     → 以 P 开头
            //   [A-Z0-9]{12} → 后面12位只能是字母或数字
            //   $      → 结束
            //   i      → 不区分大小写
            if (!preg_match('/^P[A-Z0-9]{12}$/i', $pcc)) {
                $details[] = ['row' => $i + 2, 'pcc' => $pcc, 'hname' => $name, 'status' => 'fail', 'msg' => '格式错误：需以P开头且13位'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            if (empty($name)) {
                $details[] = ['row' => $i + 2, 'pcc' => $pcc, 'hname' => '', 'status' => 'fail', 'msg' => '韩文名为空'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            if (empty($phone)) {
                $details[] = ['row' => $i + 2, 'pcc' => $pcc, 'hname' => $name, 'status' => 'fail', 'msg' => '电话为空'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            if (empty($zipcode)) {
                $details[] = ['row' => $i + 2, 'pcc' => $pcc, 'hname' => $name, 'status' => 'fail', 'msg' => '邮编为空'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            // 韩国手机号：去掉横线空格后应为 10~11 位数字
            $cleanPhone = preg_replace('/[\s-]/', '', $phone);
            if (!preg_match('/^\d{10,11}$/', $cleanPhone)) {
                $details[] = ['row' => $i + 2, 'pcc' => $pcc, 'hname' => $name, 'status' => 'fail', 'msg' => '电话格式错误'];
                $done++; $this->updateTask($taskId, $done, $details, $total); continue;
            }

            // ---------- 调用韩国海关 UNI-PASS API ----------

            $ok  = false;   // 是否校验通过
            $msg = '';      // 校验结果描述

            try {
                // 构建请求参数（匹配 verify_pcc.py 脚本）
                $params = http_build_query([
                    'crkyCn'    => 'e220w270s066w370y070x050d0',
                    'persEcm'   => $pcc,
                    'pltxNm'    => $name,
                    'cralTelno' => $cleanPhone,
                    'custPsno'  => $zipcode,
                ]);
                $queryUrl = 'https://unipass.customs.go.kr:38010/ext/rest/persEcmQry/retrievePersEcm?' . $params;

                // 每条记录之间间隔 500ms，避免触发 API 限流
                usleep(500000);

                // 使用 file_get_contents + stream context（匹配 verify_pcc.py 的 urllib 方式）
                $body = false;
                for ($attempt = 0; $attempt <= 2; $attempt++) {
                    if ($attempt > 0) usleep(1500000 * $attempt);

                    $ctx = stream_context_create(['ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name'  => false,
                    ], 'http' => [
                        'timeout' => 15,
                        'user_agent' => 'Mozilla/5.0',
                    ]]);

                    try {
                        $body = @file_get_contents($queryUrl, false, $ctx);
                        if ($body !== false) break;
                    } catch (\Exception $e) {
                        $body = false;
                    }
                }

                if ($body === false) {
                    $msg = 'API 请求失败：请检查网络';
                } else {
                    $xml = simplexml_load_string($body);

                    if ($xml && (string)$xml->tCnt === '1') {
                        $ok  = true;
                        $msg = '验证通过';
                    } else {
                        $errMsg = '';
                        $rawErr = '';
                        if ($xml && isset($xml->ntceInfo)) {
                            $rawErr = (string)$xml->ntceInfo;
                        } elseif ($xml && isset($xml->persEcmQryRtnErrInfoVo->errMsgCn)) {
                            $rawErr = (string)$xml->persEcmQryRtnErrInfoVo->errMsgCn;
                        }
                        if ($rawErr) {
                            $errMsg = $this->translatePccError($rawErr);
                        }
                        $msg = $errMsg ? "校验失败：{$errMsg}" : '校验失败：姓名/电话/邮编不匹配';
                    }
                }
            } catch (\Exception $e) {
                $msg = 'API 请求异常：' . $e->getMessage();
            }

            // ---------- 记录结果 ----------

            $details[] = [
                'row'    => $i + 2,
                'pcc'    => $pcc,
                'hname'  => $name,
                'status' => $ok ? 'pass' : 'fail',
                'msg'    => $msg,
            ];

            // 通过 → 存到 passRows；失败 → 存到 failRows（含失败原因）
            if ($ok) {
                $passRows[] = [$pcc, $name, $phone, $zipcode, $ename];
            } else {
                $failRows[] = [$pcc, $name, $phone, $zipcode, $ename, $msg];
            }

            $done++;
            $this->updateTask($taskId, $done, $details, $total);
        }

        // ===== 全部处理完毕后，生成结果 Excel =====
        $passDownloadId = (string) Str::uuid();   // 通过数据的下载 ID
        $failDownloadId = (string) Str::uuid();   // 失败数据的下载 ID
        $dir = storage_path('app/pcc_results');
        
        // 如果目录不存在就创建
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ---------- 生成「通过」Excel ----------
        if (!empty($passRows)) {
            $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $ws = $wb->getActiveSheet();
            $ws->setCellValue('A1', '通关编码(PCC)');
            $ws->setCellValue('B1', '韩文名');
            $ws->setCellValue('C1', '电话');
            $ws->setCellValue('D1', '邮编');
            $ws->setCellValue('E1', '英文名');
            foreach ($passRows as $idx => $r) {
                $rowNum = $idx + 2;
                $ws->setCellValue("A{$rowNum}", $r[0]);
                $ws->setCellValue("B{$rowNum}", $r[1]);
                $ws->setCellValue("C{$rowNum}", $r[2]);
                $ws->setCellValue("D{$rowNum}", $r[3]);
                $ws->setCellValue("E{$rowNum}", $r[4]);
            }
            $writer = new Xlsx($wb);
            $writer->save("{$dir}/{$passDownloadId}.xlsx");
        }

        // ---------- 生成「失败」Excel（含失败原因列） ----------
        if (!empty($failRows)) {
            $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $ws = $wb->getActiveSheet();
            $ws->setCellValue('A1', '通关编码(PCC)');
            $ws->setCellValue('B1', '韩文名');
            $ws->setCellValue('C1', '电话');
            $ws->setCellValue('D1', '邮编');
            $ws->setCellValue('E1', '英文名');
            $ws->setCellValue('F1', '失败原因');
            foreach ($failRows as $idx => $r) {
                $rowNum = $idx + 2;
                $ws->setCellValue("A{$rowNum}", $r[0]);
                $ws->setCellValue("B{$rowNum}", $r[1]);
                $ws->setCellValue("C{$rowNum}", $r[2]);
                $ws->setCellValue("D{$rowNum}", $r[3]);
                $ws->setCellValue("E{$rowNum}", $r[4]);
                $ws->setCellValue("F{$rowNum}", $r[5]);
            }
            $writer = new Xlsx($wb);
            $writer->save("{$dir}/{$failDownloadId}.xlsx");
        }

        // ===== 更新任务状态为已完成 =====
        $passCount = count($passRows);
        $failCount = count($failRows);

        cache()->put("pcc_task_{$taskId}", [
            'complete'         => true,
            'total'            => $done,
            'done'             => $done,
            'pass'             => $passCount,
            'fail'             => $failCount,
            'details'          => $details,
            'download_id'      => $passDownloadId,
            'download_id_fail' => $failDownloadId,
        ], 3600);
    }

    /**
     * 更新缓存中的任务进度
     * 
     * 每校验完一条就会调用一次，这样前端能实时看到进度
     * 
     * @param string $taskId  任务 ID
     * @param int    $done    已处理条数
     * @param array  $details 校验结果详情
     * @param int    $total   总条数
     */
    private function updateTask($taskId, $done, $details, $total)
    {
        // 从缓存读取当前任务数据
        $task = cache()->get("pcc_task_{$taskId}");
        if ($task) {
            // 更新已处理数量和详情
            $task['done']    = $done;
            $task['details'] = $details;
            // 重新写回缓存
            cache()->put("pcc_task_{$taskId}", $task, 3600);
        }
    }
}
