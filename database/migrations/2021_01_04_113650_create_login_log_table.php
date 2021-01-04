<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->comment("用户id");
            $table->string('ip')->comment("登录ip");
            $table->string('login_address')->comment("登录地址");
            $table->timestamp('login_time')->comment("登录时间");
            $table->tinyInteger('is_success')->comment("是否成功1成功0失败");
            $table->timestamps();
            $table->comment = "用户登录日志表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_log');
    }
}
