<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryLog extends Model
{
    protected $fillable = ['user_id', 'tool', 'query_key', 'status', 'result_count'];

    protected $casts = [
        'result_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public static function toolName(string $tool): string
    {
        return match ($tool) {
            'pcc' => 'PCC 通关编码校验',
            'rakuten' => '乐天轨迹查询',
            'customs' => '清关轨迹查询',
            'hualei' => '华磊轨迹查询',
            default => $tool,
        };
    }
}
