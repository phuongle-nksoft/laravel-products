<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('sku')->unique();
            $table->unsignedBigInteger('regions_id')->index('products_regions_id_index');
            $table->unsignedBigInteger('brands_id')->index('products_brands_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->integer('order_by')->nullable()->default(0);
            $table->integer('year_of_manufacture')->nullable()->default(1790);
            $table->decimal('price', 12, 2)->nullable()->default(0);
            $table->decimal('special_price', 12, 2)->nullable()->default(0);
            $table->decimal('alcohol_content', 12, 2)->nullable()->default(0);
            $table->decimal('volume', 12, 2)->nullable()->default(0);
            $table->longText('smell')->nullable();
            $table->integer('views')->nullable()->default(0);
            $table->decimal('rate', 4, 2)->nullable()->default(0);
            $table->longText('description')->nullable();
            $table->string('slug')->unique();
            $table->string('video_id')->nullable();
            $table->text('meta_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('regions_id', 'products_regions_id_foreign')->references('id')->on('regions')->onDelete('cascade');
            $table->foreign('brands_id', 'products_brands_id_foreign')->references('id')->on('brands')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_regions_id_foreign');
            $table->dropForeign('products_brands_id_foreign');
            $table->dropIndex('products_regions_id_index');
            $table->dropIndex('products_brands_id_index');
        });
        Schema::dropIfExists('products');
    }
}
