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
            $table->unsignedBigInteger('products_id')->index();
            $table->unsignedBigInteger('customers_id')->index();
            $table->unsignedBigInteger('shippings_id')->index();
            $table->integer('qty');
            $table->decimal('price', 12, 2)->nullable()->default(0);
            $table->decimal('toltal', 12, 2)->nullable()->default(0);
            $table->integer('status')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $this->foreign('customers_id')->references('id')->on('customers')->onDelete('cascade');
            $this->foreign('products_id')->references('id')->on('products')->onDelete('cascade');
            $this->foreign('shippings_id')->references('id')->on('shippings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('professional_ratings', function (Blueprint $table) {
            $table->dropForeign(['customers_id', 'products_id', 'shippings_id']);
            $table->dropIndex(['customers_id', 'products_id', 'shippings_id']);
        });
        Schema::dropIfExists('orders');
    }
}
