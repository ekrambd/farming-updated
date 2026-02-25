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
        Schema::create('farmeritem_farmersubcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmeritem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farmersubcategory_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('farmeritem_farmersubcategory');
    }
};
