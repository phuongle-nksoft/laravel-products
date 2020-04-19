<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customers_id')->index('orders_customers_id_index');
            $table->unsignedBigInteger('shippings_id')->index('orders_shippings_id_index');
            $table->bigInteger('promotion_id')->nullable();
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('customers_id', 'orders_customers_id_foreign')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('shippings_id', 'orders_shippings_id_foreign')->references('id')->on('shippings')->onDelete('cascade');
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
            $table->dropForeign('orders_customers_id_foreign');
            $table->dropForeign('orders_products_id_foreign');
            $table->dropForeign('orders_shippings_id_foreign');
            $table->dropIndex('orders_customers_id_index');
            $table->dropIndex('orders_products_id_index');
            $table->dropIndex('orders_shippings_id_index');
        });
        Schema::dropIfExists('orders');
    }
}
