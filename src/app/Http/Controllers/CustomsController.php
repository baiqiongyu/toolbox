<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * 韩国海关通关进度查询 (UNI-PASS)
 * 
 * API: retrieveCargCsclPrgsInfo
 * 返回 XML 结构：
 *   - cargCsclPrgsInfoQryVo → 主单信息（商品名、当前状态、海关等）
 *   - cargCsclPrgsInfoDtlQryVo[] → 详细轨迹（逐条处理记录）
 *     - prcsDttm: 处理时间 (YYYYMMDDHHMMSS)
 *     - cargTrcnRelaBsopTpcd: 处理状态
 *     - shedNm: 场所
 *     - rlbrCn: 备注
 */
class CustomsController extends Controller
{
    private $apiKey = 'b250c251u101s069l090y040t0';

    public function index()
    {
        return view('tools.customs');
    }

    public function track(Request $request)
    {
        $request->validate([
            'tracking_no' => 'required|string|max:30',
            'year'        => 'nullable|digits:4',
        ]);

        $trackingNo = trim($request->input('tracking_no'));
        $year = $request->input('year') ?? date('Y');

        try {
            $url = 'https://unipass.customs.go.kr:38010/ext/rest/cargCsclPrgsInfoQry/retrieveCargCsclPrgsInfo';

            // 使用原生 curl（兼容韩国海关的 SSL 配置）
            $queryUrl = $url . '?' . http_build_query([
                'crkyCn' => $this->apiKey,
                'hblNo'  => $trackingNo,
                'blYy'   => $year,
            ]);

            // 带重试的 curl 请求
            $body = false;
            $httpCode = 0;
            $curlErr = '';
            $maxRetries = 2;

            for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
                if ($attempt > 0) usleep(500000); // 重试前等 0.5 秒

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $queryUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
                curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
                curl_setopt($ch, CURLOPT_SSL_ENABLE_ALPN, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                $body = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($body !== false && $httpCode === 200) {
                    break; // 成功则跳出重试
                }
            }

            if ($body === false || $httpCode !== 200) {
                $errMsg = $curlErr ?: "HTTP {$httpCode}";
                QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
                return response()->json([
                    'success' => false,
                    'message' => '韩国海关 API 连接失败：' . $errMsg,
                ]);
            }

            $xml = simplexml_load_string($body);
            if ($xml === false) {
                QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
                return response()->json(['success' => false, 'message' => '海关返回数据格式异常']);
            }

            // 检查错误
            if (isset($xml->cargCsclPrgsInfoQryRtnErrInfoVo)) {
                $errMsg = (string)($xml->cargCsclPrgsInfoQryRtnErrInfoVo->errMsgCn ?? '查询失败');
                QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
                return response()->json(['success' => false, 'message' => $errMsg]);
            }

            // 检查是否有详细轨迹
            if (!isset($xml->cargCsclPrgsInfoDtlQryVo)) {
                QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
                return response()->json(['success' => false, 'message' => '未找到该单号的清关信息']);
            }

            // ===== 解析主单信息 =====
            $mainInfo = $xml->cargCsclPrgsInfoQryVo ?? null;
            $currentStatus = '';
            $productName = '';
            $customsOffice = '';
            $country = '';
            if ($mainInfo) {
                $currentStatus = (string)($mainInfo->csclPrgsStts ?? $mainInfo->prgsStts ?? '');
                $productName = (string)($mainInfo->prnm ?? '');
                $customsOffice = (string)($mainInfo->etprCstm ?? '');
                $country = (string)($mainInfo->shipNatNm ?? '');
            }

            // ===== 解析详细轨迹 =====
            $history = [];
            foreach ($xml->cargCsclPrgsInfoDtlQryVo as $vo) {
                // 处理时间：20260427110000 → 2026-04-27 11:00:00
                $rawDt = (string)($vo->prcsDttm ?? '');
                $datetime = '';
                if ($rawDt && strlen($rawDt) >= 8) {
                    $datetime = substr($rawDt, 0, 4) . '-' . substr($rawDt, 4, 2) . '-' . substr($rawDt, 6, 2);
                    if (strlen($rawDt) >= 12) {
                        $datetime .= ' ' . substr($rawDt, 8, 2) . ':' . substr($rawDt, 10, 2) . ':' . substr($rawDt, 12, 2);
                    }
                }

                // 状态文本
                $statusKo = (string)($vo->cargTrcnRelaBsopTpcd ?? '');
                // 场所
                $location = (string)($vo->shedNm ?? '');
                // 备注
                $remark = (string)($vo->rlbrCn ?? '');
                // 申报号
                $dclrNo = (string)($vo->dclrNo ?? '');
                // 重量
                $weight = (string)($vo->wght ?? '');
                $weightUt = (string)($vo->wghtUt ?? '');

                $en = $this->statusToEn($statusKo);
                $cn = $this->statusToCn($statusKo);
                // 记录未翻译的状态，方便后续补全
                if ($en === 'Customs Processing' && $statusKo !== '') {
                    \Illuminate\Support\Facades\Log::debug('UNPASS untranslated status', ['ko' => $statusKo]);
                }
                $history[] = [
                    'datetime'  => $datetime,
                    'date'      => $rawDt,
                    'time'      => '',
                    'status_ko' => $statusKo,
                    'status_en' => $en,
                    'status_cn' => $cn,
                    'location'  => $location ?: '-',
                    'remark'    => $remark,
                    'dclr_no'   => $dclrNo,
                    'weight'    => $weight ? $weight . ' ' . $weightUt : '',
                ];
            }

            if (empty($history)) {
                QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
                return response()->json(['success' => false, 'message' => '该单号暂无清关轨迹']);
            }

            // 按时间排序（原始数据通常已排好，但保险起见）
            usort($history, fn($a, $b) => $a['date'] <=> $b['date']);

            $last = end($history);
            // 如果有主单状态则优先用主单状态
            $displayStatus = $currentStatus ?: $last['status_ko'];

            QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'success', 'result_count' => count($history)]);

            return response()->json([
                'success'        => true,
                'tracking_no'    => $trackingNo,
                'status'         => $this->statusToEn($displayStatus),
                'status_cn'      => $this->statusToCn($displayStatus),
                'product_name'   => $productName,
                'customs_office' => $customsOffice,
                'country'        => $country,
                'history'        => $history,
            ]);

        } catch (\Exception $e) {
            QueryLog::create(['user_id' => auth()->id(), 'tool' => 'customs', 'query_key' => $trackingNo, 'status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => '查询异常：' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 韩文清关状态 → 中文
     */
    private function statusToCn($ko)
    {
        $map = [
            // === 入境 ===
            '입항보고 제출' => '入境申报提交',
            '입항보고 수리' => '入境申报受理',
            '입항적재화물목록 제출' => '入境载货清单提交',
            '입항적재화물목록 심사완료' => '入境载货清单审核完成',
            '입항적재화물목록 운항정보 정정' => '入境载货清单信息更正',
            '입항적재화물목록 정정' => '入境载货清单更正',
            // === 卸货 ===
            '하기신고 수리' => '卸货申报受理',
            '하선신고 수리' => '卸船申报受理',
            '하기신고 제출' => '卸货申报提交',
            // === 进港 ===
            '반입신고' => '进港申报',
            '반입신고 수리' => '进港申报受理',
            '반출신고' => '出港申报',
            '반출통고' => '出港通知',
            // === 进口申报 ===
            '수입신고' => '进口申报',
            '수입신고수리' => '进口申报放行',
            '수입신고수리 취소' => '进口申报放行取消',
            '수입(사용소비) 심사진행' => '进口审核中',
            '수입(사용소비) 심사완료' => '进口审核完成',
            '수입(사용소비) 심사' => '进口审核中',
            '수입신고 심사' => '进口申报审核中',
            // === 补充 ===
            '보완요구' => '需要补充材料',
            '보완' => '需要补充材料',
            '보완서류 제출' => '补充文件提交',
            '보완서류 접수' => '补充文件受理',
            // === 查验 ===
            '검사' => '查验中',
            '검사완료' => '查验完成',
            '검사진행' => '查验进行中',
            '검사대상' => '查验对象',
            '검사면제' => '免查验',
            // === 放行 ===
            '통관목록접수' => '通关清单受理',
            '통관목록심사완료' => '通关清单审核完成',
            '통관목록심사취소' => '通关清单审核取消',
            '통관목록보류' => '通关清单保留',
            '통관' => '已通关',
            '통관완료' => '通关完成',
            '통관허가' => '通关许可',
            '수리' => '已放行',
            '수리완료' => '放行完成',
            '자동수리' => '自动放行',
            // === 退回 ===
            '반송' => '已退回',
            '반송신고' => '退回申报',
            '반려' => '已退回',
            // === 存放 ===
            '장치' => '已存放',
            '장치기간' => '存放中',
            '보세구역' => '保税区存放',
            '보세운송' => '保税运输',
            // === 装卸 ===
            '적하' => '已装货',
            '적하완료' => '装货完成',
            '하역' => '已卸货',
            '하역완료' => '卸货完成',
            // === 出入库 ===
            '입고' => '已入库',
            '출고' => '已出库',
            '입출고' => '出入库',
            // === 运输 ===
            '운송' => '运输中',
            '운송시작' => '运输开始',
            '운송완료' => '运输完成',
            // === 到达/出发 ===
            '도착' => '已到达',
            '출발' => '已出发',
            '도착예정' => '预计到达',
            // === 关税 ===
            '관세납부' => '关税缴纳',
            '관세확인' => '关税确认',
            '납부완료' => '缴税完成',
            '면세' => '免税',
            // === 其他 ===
            '신고' => '已申报',
            '접수' => '已受理',
            '처리중' => '处理中',
            '처리완료' => '处理完成',
            '승인' => '已批准',
            '거부' => '已拒绝',
            '취소' => '已取消',
            '취하' => '已撤回',
            '무효' => '已作废',
            '오류' => '数据错误',
            '확인' => '确认中',
            '대기' => '等待中',
            '지연' => '已延迟',
            '종료' => '已结束',
        ];
        return $map[$ko] ?? $ko;
    }

    /**
     * 韩文清关状态 → 英文
     */
    private function statusToEn($ko)
    {
        $map = [
            // Arrival
            '입항보고 제출' => 'Arrival Report Submitted',
            '입항보고 수리' => 'Arrival Report Approved',
            '입항적재화물목록 제출' => 'Cargo Manifest Submitted',
            '입항적재화물목록 심사완료' => 'Cargo Manifest Reviewed',
            '입항적재화물목록 운항정보 정정' => 'Cargo Manifest Corrected',
            '입항적재화물목록 정정' => 'Cargo Manifest Corrected',
            // Unload
            '하기신고 수리' => 'Unload Report Approved',
            '하선신고 수리' => 'Discharge Report Approved',
            '하기신고 제출' => 'Unload Report Submitted',
            // Entry
            '반입신고' => 'Entry Declaration',
            '반입신고 수리' => 'Entry Declaration Approved',
            '반출신고' => 'Export Declaration',
            '반출통고' => 'Export Notice',
            // Import
            '수입신고' => 'Import Declaration',
            '수입신고수리' => 'Import Release',
            '수입신고수리 취소' => 'Import Release Cancelled',
            '수입(사용소비) 심사진행' => 'Import Under Review',
            '수입(사용소비) 심사완료' => 'Import Review Completed',
            '수입(사용소비) 심사' => 'Import Under Review',
            '수입신고 심사' => 'Import Under Review',
            // Supplement
            '보완요구' => 'Additional Info Required',
            '보완' => 'Additional Info Required',
            '보완서류 제출' => 'Supplement Documents Submitted',
            '보완서류 접수' => 'Supplement Documents Received',
            // Inspection
            '검사' => 'Under Inspection',
            '검사완료' => 'Inspection Completed',
            '검사진행' => 'Inspection In Progress',
            '검사대상' => 'Subject to Inspection',
            '검사면제' => 'Inspection Exempted',
            // Clearance
            '통관목록접수' => 'Customs List Received',
            '통관목록심사완료' => 'Customs List Review Completed',
            '통관목록심사취소' => 'Customs List Review Cancelled',
            '통관목록보류' => 'Customs List On Hold',
            '통관' => 'Cleared',
            '통관완료' => 'Cleared',
            '통관허가' => 'Clearance Permitted',
            '수리' => 'Approved',
            '수리완료' => 'Approved',
            '자동수리' => 'Auto Approved',
            // Return
            '반송' => 'Returned',
            '반송신고' => 'Return Declared',
            '반려' => 'Returned',
            // Storage
            '장치' => 'In Storage',
            '장치기간' => 'In Storage',
            '보세구역' => 'Bonded Area',
            '보세운송' => 'Bonded Transport',
            // Load/Unload
            '적하' => 'Loaded',
            '적하완료' => 'Loading Completed',
            '하역' => 'Unloaded',
            '하역완료' => 'Unloading Completed',
            // Warehouse
            '입고' => 'Inbound',
            '출고' => 'Outbound',
            '입출고' => 'In/Outbound',
            // Transport
            '운송' => 'In Transit',
            '운송시작' => 'Transit Started',
            '운송완료' => 'Transit Completed',
            // Arrive/Depart
            '도착' => 'Arrived',
            '출발' => 'Departed',
            '도착예정' => 'Estimated Arrival',
            // Duty
            '관세납부' => 'Duty Payment',
            '관세확인' => 'Duty Confirmation',
            '납부완료' => 'Payment Completed',
            '면세' => 'Duty Free',
            // Other
            '신고' => 'Declared',
            '접수' => 'Received',
            '처리중' => 'Processing',
            '처리완료' => 'Completed',
            '승인' => 'Approved',
            '거부' => 'Rejected',
            '취소' => 'Cancelled',
            '취하' => 'Withdrawn',
            '무효' => 'Voided',
            '오류' => 'Error',
            '확인' => 'Confirming',
            '대기' => 'Pending',
            '지연' => 'Delayed',
            '종료' => 'Ended',
        ];
        return $map[$ko] ?? 'Customs Processing';
    }
}
