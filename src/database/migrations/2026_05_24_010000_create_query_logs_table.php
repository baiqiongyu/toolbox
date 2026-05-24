<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('query_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tool', 50)->comment('工具名: pcc/rakuten/customs/hualei');
            $table->string('query_key')->comment('查询关键字: 单号/文件名');
            $table->string('status', 20)->default('success')->comment('success/failed');
            $table->integer('result_count')->default(0)->comment('结果条数');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_logs');
    }
};
