<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string("name")->comment("工具名称");
            $table->string("icon")->comment("图标， 如 emoji或图标类名");
            $table->string("route")->unique()->comment("路由地址， 如 /tools/pcc");
            $table->string("color", 20)->default("#3498db")->comment("图标背景颜色");
            $table->text("description")->nullable()->comment("工具描述");
            $table->boolean("enabled")->default(true)->comment("是否启用");
            $table->integer("sort_order")->default(0)->comment("排序");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
