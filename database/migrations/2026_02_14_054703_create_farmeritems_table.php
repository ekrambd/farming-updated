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
        Schema::create('farmeritems', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('farmerunit_id');
            $table->integer('farmercategory_id');
            $table->integer('farmersubcategory_id')->nullable();
            $table->string('item_name');
            $table->string('item_name_bn');
            $table->string('price');
            $table->string('discount')->default('0')->nullable();
            $table->string('stock_qty');
            $table->text('description');
            $table->string('featured_image');
            $table->enum('status', ['Active', 'Inactive']);
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
        Schema::dropIfExists('farmeritems');
    }
};
