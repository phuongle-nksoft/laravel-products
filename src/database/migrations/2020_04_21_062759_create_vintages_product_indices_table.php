<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVintagesProductIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vintages_product_indices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vintages_id')->index('vintages_product_indices_vintages_id_index');
            $table->unsignedBigInteger('products_id')->index('vintages_product_indices_products_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('vintages_id', 'vintages_product_indices_vintages_id_foreign')->references('id')->on('vintages')->onDelete('cascade');
            $table->foreign('products_id', 'vintages_product_indices_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vintages_product_indices', function (Blueprint $table) {
            $table->dropForeign('vintages_product_indices_vintages_id_foreign');
            $table->dropIndex('vintages_product_indices_vintages_id_index');
            $table->dropForeign('vintages_product_indices_products_id_foreign');
            $table->dropIndex('vintages_product_indices_products_id_index');
        });
        Schema::dropIfExists('vintages_product_indices');
    }
}
