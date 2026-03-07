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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('cart_subtotal')->nullable();
            $table->string('delivery_charge')->nullable();
            $table->string('vat_tax')->nullable();
            $table->string('cart_total')->nullable();
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending')->nullable();
            $table->string('order_type')->nullable();
            $table->string('trx_id')->nullable();
            $table->string('order_total')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
