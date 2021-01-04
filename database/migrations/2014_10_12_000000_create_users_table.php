<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment("姓名");
            $table->string('username')->comment("用户名");
            $table->string('password')->comment("用户密码");
            $table->char('phone', 11)->comment("手机号");
            $table->string('id_card', 18)->comment("身份证");
            $table->string('email')->nullable()->comment("邮箱");
            $table->string('avatar')->comment("头像");
            $table->tinyInteger('sex')->comment("性别");
            $table->tinyInteger('status')->comment("状态0禁用1启用2冻结");
            $table->tinyInteger('is_super')->comment("是否超级账号");
            $table->string("birthday")->nullable()->comment("出生日期");
            $table->string("address")->nullable()->comment("联系地址");
            $table->string("description")->nullable()->comment("备注信息");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
