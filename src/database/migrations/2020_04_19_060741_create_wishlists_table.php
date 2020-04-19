<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWishlistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id')->index('wishlists_products_id_index');
            $table->unsignedBigInteger('customers_id')->index('wishlists_customers_id_index');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('products_id', 'wishlists_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('customers_id', 'wishlists_customers_id_foreign')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign('wishlists_products_id_foreign');
            $table->dropForeign('wishlists_customers_id_foreign');
            $table->dropIndex('wishlists_customers_id_index');
            $table->dropIndex('wishlists_products_id_index');
        });
        Schema::dropIfExists('wishlists');
    }
}
