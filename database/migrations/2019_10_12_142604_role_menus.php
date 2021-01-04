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
            $table->increments("id");
            $table->integer("role_id");
            $table->integer("menu_id");
            $table->foreign('menu_id')->references('id')->on('menus')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['menu_id', 'role_id']);
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
