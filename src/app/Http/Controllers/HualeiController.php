<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * 华磊物流轨迹查询
 * 
 * API: https://sapi.e-kc.vip/HLApi/queryTrackList
 * POST {documentCode: [单号]}
 */
class HualeiController extends Controller
{
    public function index()
    {
        return view('tools.hualei');
    }

    public function track(Request $request)
    {
        $request->validate([
            'tracking_no' => 'required|string|max:30',
        ]);

        $trackingNo = trim($request->input('tracking_no'));

        try {
            // 最多尝试 3 次
            $result = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                if ($attempt > 0) usleep(800000);

                $response = Http::timeout(30)
                    ->withOptions(['verify' => false])
                    ->withHeaders([
                        'Accept' => 'application/json, text/plain',
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://sapi.e-kc.vip/HLApi/queryTrackList', [
                        'documentCode' => [$trackingNo],
                    ]);

                if ($response->failed()) continue;

                // 手动解析 body（API 返回的 JSON 可能经过二次编码）
                $body = $response->body();
                $data = json_decode($body, true);
                // 如果一次解码后还是字符串，说明是二次编码，再解一次
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }

                if (is_array($data) && ($data['status'] ?? 0) === 200 && !empty($data['data'])) {
                    $result = $data;
                    break;
                }
            }

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到该单号的物流信息，请稍后重试',
                ]);
            }

            // 提取该单号的轨迹
            $item = null;
            foreach ($result['data'] as $d) {
                if (($d['trackingNumber'] ?? '') === $trackingNo) {
                    $item = $d;
                    break;
                }
            }

            if (!$item || empty($item['trackDetails'])) {
                return response()->json([
                    'success' => false,
                    'message' => '该单号暂无轨迹信息',
                ]);
            }

            $details = $item['trackDetails'];

            // 过滤"货物电子信息已经收到"
            $details = array_values(array_filter($details, function ($d) {
                return ($d['track_content'] ?? '') !== '货物电子信息已经收到';
            }));

            // 按时间排序
            usort($details, function ($a, $b) {
                return strtotime($a['track_date'] ?? '') - strtotime($b['track_date'] ?? '');
            });

            $history = [];
            foreach ($details as $d) {
                $history[] = [
                    'datetime'  => $d['track_date'] ?? '',
                    'status_cn' => $d['track_content'] ?? '',
                    'location'  => $d['track_location'] ?: ($d['track_city'] ?: '-'),
                ];
            }

            $last = end($history);

            return response()->json([
                'success'     => true,
                'tracking_no' => $trackingNo,
                'status_cn'   => $last['status_cn'] ?? '运输中',
                'history'     => $history,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询异常：' . $e->getMessage(),
            ]);
        }
    }
}
