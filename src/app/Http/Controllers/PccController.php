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

            // ---------- 调用韩国海关 UNI-PASS API ----------

            $ok  = false;   // 是否校验通过
            $msg = '';      // 校验结果描述

            try {
                // Http::timeout(10)->get(...) 是 Laravel 的 HTTP 客户端
                // timeout(10) 表示10秒超时
                // get() 表示 GET 请求，参数通过数组传进去
                $response = Http::timeout(10)->get('https://unipass.customs.go.kr:38010/ext/rest/persEcmQry/retrievePersEcm', [
                    'crkyCn'   => 'w270p240e076b360l000j000t0',   // API 密钥
                    'persEcm'  => $pcc,                            // PCC 编码
                    'pltxNm'   => $name,                           // 韩文名
                    'cralTelno' => preg_replace('/[\s-]/', '', $phone),  // 电话（去掉空格和横线）
                    'custPsno' => $zipcode,                        // 邮编
                ]);

                // API 返回的是 XML 格式，用 simplexml_load_string() 解析
                $xml = simplexml_load_string($response->body());

                // tCnt 为 1 表示验证通过
                if ($xml && (string)$xml->tCnt === '1') {
                    $ok  = true;
                    $msg = '验证通过';
                } else {
                    // 如果 API 返回了错误信息，提取出来展示
                    $errMsg = '';
                    if ($xml && isset($xml->persEcmQryRtnErrInfoVo->errMsgCn)) {
                        $errMsg = (string)$xml->persEcmQryRtnErrInfoVo->errMsgCn;
                    }
                    $msg = $errMsg ? "校验失败：{$errMsg}" : '校验失败：姓名/电话/邮编不匹配';
                }
            } catch (\Exception $e) {
                // 网络超时或其它异常
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
