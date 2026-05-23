<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 工具模型类
 *
 * 用于管理系统中的工具信息，包括工具的基本属性、显示配置和启用状态
 */
class Tool extends Model
{
    /**
     * 可批量赋值的属性字段
     *
     * 允许通过 create() 或 update() 方法直接赋值的字段列表
     *
     * @var string[]
     */
    protected $fillable = ['name', 'icon', 'route', 'color', 'description', 'enabled', 'sort_order'];

    /**
     * 属性类型转换规则
     *
     * 将数据库字段转换为特定的 PHP 数据类型
     * enabled 字段会被自动转换为布尔类型
     *
     * @var string[]
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * 询作用域：仅获取已启用的工具并按排序字段排序
     *
     * 该方法定义了一个局部作用域，用于筛选出所有启用的工具记录，
     * 并按照 sort_order 字段进行升序排列
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true)->orderBy('sort_order');
    }
}
