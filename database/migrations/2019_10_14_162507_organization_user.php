<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrganizationUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id")->comment("用户id");
            $table->integer("organization_id")->comment("组织id");
            $table->timestamps();
            $table->comment = "组织-用户关系表";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("ORGANIZATION_USER");
    }
}
