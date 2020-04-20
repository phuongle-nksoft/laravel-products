<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tags_id')->index('product_tags_tags_id_index');
            $table->unsignedBigInteger('products_id')->index('product_tags_products_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('tags_id', 'product_tags_tags_id_foreign')->references('id')->on('tags')->onDelete('cascade');
            $table->foreign('products_id', 'product_tags_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_tags', function (Blueprint $table) {
            $table->dropForeign('product_tags_tags_id_foreign');
            $table->dropIndex('product_tags_tags_id_index');
            $table->dropForeign('product_tags_products_id_foreign');
            $table->dropIndex('product_tags_products_id_index');
        });
        Schema::dropIfExists('product_tags');
    }
}
