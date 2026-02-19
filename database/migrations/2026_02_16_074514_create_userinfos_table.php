<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userinfos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('farmercategory_id')->nullable();
            $table->integer('farmersubcategory_id')->nullable();
            $table->text('businees_location')->nullable();
            $table->text('businees_address')->nullable();
            $table->string('nid_passport')->nullable();
            $table->string('nid_front_photo')->nullable();
            $table->string('nid_back_photo')->nullable();
            $table->string('trade_license_photo')->nullable();
            $table->string('refer_id')->nullable();
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
        Schema::dropIfExists('userinfos');
    }
};
