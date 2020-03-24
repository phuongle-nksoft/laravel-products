<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id')->index('ratings_products_id_index');
            $table->longText('description')->nullable();
            $table->double('ratings', 8, 2)->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('products_id', 'ratings_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign('ratings_products_id_foreign');
            $table->dropIndex('ratings_products_id_index');
        });
        Schema::dropIfExists('ratings');
    }
}
