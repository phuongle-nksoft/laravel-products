<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id')->index('product_comments_products_id_index');
            $table->unsignedBigInteger('customers_id')->index('product_comments_customers_id_index');
            $table->string('name')->nullable();
            $table->text('description');
            $table->bigInteger('parent_id')->default(0);
            $table->integer('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('products_id', 'product_comments_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('customers_id', 'product_comments_customers_id_foreign')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_comments', function (Blueprint $table) {
            $table->dropForeign('product_comments_products_id_foreign');
            $table->dropForeign('product_comments_customers_id_foreign');
            $table->dropIndex('product_comments_customers_id_index');
            $table->dropIndex('product_comments_products_id_index');
        });
        Schema::dropIfExists('product_comments');
    }
}
