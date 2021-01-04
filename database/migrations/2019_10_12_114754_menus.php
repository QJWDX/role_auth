<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Menus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("menus", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("parent_id")->default(0)->comment("上级id,(0表示顶级)");
            $table->string("name")->comment("菜单名称");
            $table->string("icon")->nullable()->comment("图标");
            $table->string("path")->comment("url");
            $table->string("component")->nullable()->comment('前端组建地址');
            $table->tinyInteger("status")->default(1)->comment("是否展示（默认是）(1-是，2-否)");
            $table->tinyInteger("is_related_route")->default(2)->comment("是否关联路由");
            $table->tinyInteger("is_default")->default(2)->comment("是否为默认路由，即系统的首页(1-是，2-否)");
            $table->integer("sort")->default(0)->comment("排序，0为顶级");
            $table->timestamps();
            $table->comment = '菜单表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("MENUS");
    }
}
