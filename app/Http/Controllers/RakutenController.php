<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * 韩国乐天物流 (Lotte Global Logistics) 轨迹查询
 * 
 * 通过官方 OpenAPI 获取追踪数据
 */
class RakutenController extends Controller
{
    private $apiUrl = 'https://ftr.alps.llogis.com:18260/openapi/ftr/getPaclTrackingTp01';
    private $authorization = 'IgtAK eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJDMDE0ODQ2IiwiYXVkIjoiQzAxNDg0NiIsIm5hbWUiOiJob3RzaW4iLCJzY29wZSI6IlJTX0FERFIiLCJleHAiOjE1MzUxMzU1OTk5OTksImlhdCI6MTcxNDQ2MTg2NX0.hD-fHg0qCAZlJpuBp7u5Zz5b7b2Ir70JU7a9VvCbTgs';
    private $superCustCd = 'LGLCBE';

    public function index()
    {
        return view('tools.rakuten');
    }

    /**
     * 查询单号轨迹
     */
    public function track(Request $request)
    {
        $request->validate([
            'tracking_no' => 'required|string|max:30',
        ]);

        $trackingNo = trim($request->input('tracking_no'));

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'charset'       => 'utf-8',
                    'Authorization' => $this->authorization,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'INV_NO'        => $trackingNo,
                    'SUPER_CUST_CD' => $this->superCustCd,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => '乐天API暂时无法连接（HTTP ' . $response->status() . '）',
                ]);
            }

            $result = $response->json();

            if (isset($result['code']) && $result['code'] === 'S') {
                // TRACK_LIST 是多包裹数组，取第一个包裹
                $parcel = $result['TRACK_LIST'][0] ?? [];

                // 轨迹在 TRACKING 数组中
                $tracking = $parcel['TRACKING'] ?? [];

                // 当前状态
                $currentStatus = $parcel['CUR_PACL_STATUS'] ?? [];

                $history = $this->formatTracking($tracking);

                if (empty($history)) {
                    return response()->json([
                        'success' => false,
                        'message' => '该单号暂无轨迹信息',
                    ]);
                }

                $last = end($history);

                return response()->json([
                    'success'     => true,
                    'tracking_no' => $trackingNo,
                    'status'      => $last['status_en'],
                    'status_cn'   => $last['status_cn'],
                    'history'     => $history,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? '查询失败，乐天API返回异常',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询异常：' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 格式化 TRACKING 数组
     * 
     * API 返回的每个轨迹节点格式：
     * - SCAN_YMD: 日期 (20260522)
     * - SCAN_TME: 时间 (112817)
     * - GODS_STAT_NM: 状态简称 (집하, 셔틀발송, 배달전, 배달완료...)
     * - STATUS: 状态描述 (물품을 보내셨습니다.)
     * - BRNSHP_NM: 操作网点
     */
    private function formatTracking($tracking)
    {
        $history = [];

        foreach ($tracking as $item) {
            // 日期时间
            $ymd = $item['SCAN_YMD'] ?? '';
            $tme = $item['SCAN_TME'] ?? '';
            $datetime = '';
            if ($ymd) {
                $datetime = substr($ymd, 0, 4) . '-' . substr($ymd, 4, 2) . '-' . substr($ymd, 6, 2);
                if ($tme) {
                    $datetime .= ' ' . substr($tme, 0, 2) . ':' . substr($tme, 2, 2) . ':' . substr($tme, 4, 2);
                }
            }

            // 状态：优先用 GODS_STAT_NM（简称），STATUS 作为备注
            $statusKo = $item['GODS_STAT_NM'] ?? '';
            $statusDetail = $item['STATUS'] ?? '';

            // 地点
            $location = $item['BRNSHP_NM'] ?? '';

            $history[] = [
                'datetime'  => $datetime,
                'date'      => $ymd,
                'time'      => $tme,
                'status_ko' => $statusKo,
                'status_en' => $this->koToEn($statusKo),
                'status_cn' => $this->koToCn($statusKo),
                'location'  => $location ?: '-',
                'remark'    => $statusDetail,
            ];
        }

        // 按时间正序
        usort($history, function ($a, $b) {
            return ($a['date'] . $a['time']) <=> ($b['date'] . $b['time']);
        });

        return $history;
    }

    private function koToCn($ko)
    {
        $map = [
            '집하' => '已揽收', '집화' => '已揽收',
            '운송장등록' => '运单登记',
            '셔틀발송' => '干线发出', '셔틀도착' => '干线到达',
            '적입' => '装载中', '해체' => '卸货中',
            '배달전' => '派送准备', '배달준비' => '派送准备',
            '배달출발' => '派送中',
            '배달완료' => '已签收',
            '인수자등록' => '收件人确认',
            '도착' => '已到达', '출발' => '已出发',
            '간선발송' => '干线发出', '간선도착' => '干线到达',
            '물류센터도착' => '到达物流中心', '물류센터출발' => '离开物流中心',
            '통관중' => '清关中', '통관완료' => '清关完成',
            '검사중' => '检查中', '반송' => '退回',
            '배송지연' => '配送延迟', '배송예정' => '预计配送',
            '수거완료' => '已取件',
            '입고' => '已入库', '출고' => '已出库',
        ];
        return $map[$ko] ?? $ko;
    }

    private function koToEn($ko)
    {
        $map = [
            '집하' => 'Collected', '집화' => 'Collected',
            '운송장등록' => 'Waybill Registered',
            '셔틀발송' => 'Departed from Hub', '셔틀도착' => 'Arrived at Hub',
            '적입' => 'Loading', '해체' => 'Unloading',
            '배달전' => 'Preparing Delivery', '배달준비' => 'Preparing Delivery',
            '배달출발' => 'Out for Delivery',
            '배달완료' => 'Delivered',
            '인수자등록' => 'Recipient Confirmed',
            '도착' => 'Arrived', '출발' => 'Departed',
            '간선발송' => 'Departed from Hub', '간선도착' => 'Arrived at Hub',
            '물류센터도착' => 'Arrived at Center', '물류센터출발' => 'Departed from Center',
            '통관중' => 'Customs in Progress', '통관완료' => 'Customs Completed',
            '검사중' => 'Inspecting', '반송' => 'Returned',
            '배송지연' => 'Delivery Delayed', '배송예정' => 'Delivery Scheduled',
            '수거완료' => 'Picked Up',
            '입고' => 'Inbound', '출고' => 'Outbound',
        ];
        return $map[$ko] ?? $ko;
    }
}
