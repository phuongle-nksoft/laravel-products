<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductsIndicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_products_indices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('categories_id')->index('category_products_indices_categories_id_index');
            $table->unsignedBigInteger('products_id')->index('category_products_indices_products_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('categories_id', 'category_products_indices_categories_id_foreign')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('products_id', 'category_products_indices_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_products_indices', function (Blueprint $table) {
            $table->dropForeign('category_products_indices_categories_id_foreign');
            $table->dropIndex('category_products_indices_categories_id_index');
            $table->dropForeign('category_products_indices_products_id_foreign');
            $table->dropIndex('category_products_indices_products_id_index');
        });
        Schema::dropIfExists('category_products_indices');
    }
}
