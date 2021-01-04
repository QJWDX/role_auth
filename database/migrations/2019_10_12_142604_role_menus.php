<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoleMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("role_menus", function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments("id");
            $table->integer("role_id");
            $table->integer("menu_id");
            $table->timestamps();
            $table->comment = '角色菜单关联表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("ROLE_MENUS");
    }
}
