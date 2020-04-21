<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('child_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->index('child_products_parent_id_index');
            $table->unsignedBigInteger('child_id')->index('child_products_child_id_index');
            $table->boolean('is_active')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('parent_id', 'child_products_parent_id_foreign')->references('id')->on('tags')->onDelete('cascade');
            $table->foreign('child_id', 'child_products_child_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('child_products', function (Blueprint $table) {
            $table->dropForeign('child_products_child_id_foreign');
            $table->dropIndex('child_products_parent_id_foreign');
            $table->dropForeign('child_products_child_id_index');
            $table->dropIndex('child_products_parent_id_index');
        });
        Schema::dropIfExists('child_products');
    }
}
