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
        Schema::create('farmercategory_farmeritem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmercategory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farmeritem_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('farmercategory_farmeritem');
    }
};
