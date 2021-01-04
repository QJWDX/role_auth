<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Organization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->comment("组织名称");
            $table->integer("parent_id")->comment("父类id(顶级为0，即为系统组织)");
            $table->string("code")->nullable()->comment("内部编号");
            $table->integer("sort")->default(0)->comment("排序编号");
            $table->string("content", 500)->nullable()->comment("备注信息");
            $table->timestamps();
            $table->comment = "组织表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("Organization");
    }
}
