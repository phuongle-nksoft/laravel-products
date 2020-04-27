<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_optionals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('products_id')->index('product_optionals_products_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->integer('order_by')->nullable()->default(0);
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();
            $table->string('video_id')->nullable();
            $table->text('meta_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('products_id', 'product_optionals_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_optionals', function (Blueprint $table) {
            $table->dropForeign('product_optionals_products_id_foreign');
            $table->dropIndex('product_optionals_products_id_index');
        });
        Schema::dropIfExists('product_optionals');
    }
}
