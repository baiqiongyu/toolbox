<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        Tool::create([
            'name' => 'PCC 通关编码校验',
            'icon' => '📋',
            'route' => '/tools/pcc',
            'color' => '#3498db',
            'description' => '批量校验韩国海关PCC通关编码，上传Excel一键验证',
            'sort_order' => 1,
        ]);

        Tool::create([
            'name' => '乐天轨迹查询',
            'icon' => '📦',
            'route' => '/tools/rakuten',
            'color' => '#e67e22',
            'description' => '查询韩国乐天物流(Lotte)配送轨迹信息',
            'sort_order' => 2,
        ]);

        Tool::create([
            'name' => '清关轨迹查询',
            'icon' => '🛃',
            'route' => '/tools/customs',
            'color' => '#9b59b6',
            'description' => '查询跨境包裹清关状态及通关进度',
            'sort_order' => 3,
        ]);

        Tool::create([
            'name' => '华磊轨迹查询',
            'icon' => '🚚',
            'route' => '/tools/hualei',
            'color' => '#059669',
            'description' => '华磊物流快递单号轨迹查询',
            'sort_order' => 4,
        ]);
    }
}
