<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id')->index();
            $table->unsignedBigInteger('orders_id')->index();
            $table->integer('status');
            $table->unsignedBigInteger('payment_methods_id')->index();
            $table->timestamps();
            $this->foreign('orders_id')->references('id')->on('orders')->onDelete('cascade');
            $this->foreign('products_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
