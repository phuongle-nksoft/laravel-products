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
            $table->unsignedBigInteger('products_id')->index('payments_products_id_index');
            $table->unsignedBigInteger('orders_id')->index('payments_orders_id_index');
            $table->integer('status');
            $table->unsignedBigInteger('payment_methods_id')->index('payments_payment_methods_id_index');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('orders_id', 'payments_orders_id_foreign')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('products_id', 'payments_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('payment_methods_id', 'payments_payment_methods_id_foreign')->references('id')->on('payment_methods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_payment_methods_id_foreign');
            $table->dropForeign('payments_products_id_foreign');
            $table->dropForeign('payments_orders_id_foreign');
            $table->dropIndex('payments_payment_methods_id_index');
            $table->dropIndex('payments_orders_id_index');
            $table->dropIndex('payments_products_id_index');
        });
        Schema::dropIfExists('payments');
    }
}
