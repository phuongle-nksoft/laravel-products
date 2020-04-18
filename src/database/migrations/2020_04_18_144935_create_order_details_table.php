<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orders_id')->index('order_details_orders_id_index');
            $table->unsignedBigInteger('products_id')->index('order_details_products_id_index');
            $table->integer('qty');
            $table->decimal('price', 12, 2)->nullable()->default(0);
            $table->decimal('toltal', 12, 2)->nullable()->default(0);
            $table->bigInteger('promotion_id')->nullable();
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('orders_id', 'order_details_orders_id_foreign')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('products_id', 'order_details_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('order_details_orders_id_foreign');
            $table->dropForeign('order_details_products_id_foreign');
            $table->dropForeign('orders_shippings_id_foreign');
            $table->dropIndex('order_details_orders_id_index');
            $table->dropIndex('order_details_products_id_index');
        });
        Schema::dropIfExists('order_details');
    }
}
