<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EntrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        // 角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->unique()->comment("角色名称");
            $table->string('display_name')->nullable()->comment("显示名称");
            $table->tinyInteger('is_super')->default(0)->comment("是否超级角色");
            $table->string('remark')->nullable()->comment("角色备注");
            $table->timestamps();
            $table->comment = "角色表";
        });

        //用户与角色关联表
        Schema::create('role_user', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
            $table->comment = "用户与角色关联表";
        });

        // 权限表
        Schema::create('permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string("path")->comment("路由url");
            $table->enum("method", ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])->comment("http请求方式（GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD）");
            $table->integer("parent_id")->default(0)->comment("父级id");
            $table->integer("level")->default(1)->comment("级别");
            $table->integer("is_show")->default(1)->comment("是否显示(1显示0否)");
            $table->string('description')->nullable();
            $table->timestamps();
            $table->comment = "权限表";
        });

        //角色与权限关联表
        Schema::create('permission_role', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
            $table->comment = "角色与权限关联表";
        });

        // 权限与菜单关联表
        Schema::create("permission_menus", function(Blueprint $table){
            $table->engine = 'InnoDB';
            $table->integer("permission_id");
            $table->integer("menu_id");
            $table->comment = "权限与菜单关联表";
        });

        \Illuminate\Support\Facades\DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
}
