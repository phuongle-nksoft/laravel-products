<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professional_ratings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('professionals_id')->index('professional_ratings_professionals_id_index');
            $table->unsignedBigInteger('products_id')->index('professional_ratings_products_id_index');
            $table->longText('description')->nullable();
            $table->double('ratings', 8, 2)->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('professionals_id', 'professional_ratings_professionals_id_foreign')->references('id')->on('professionals')->onDelete('cascade');
            $table->foreign('products_id', 'professional_ratings_products_id_foreign')->references('id')->on('products')->onDelete('cascade');
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
            $table->dropForeign('professional_ratings_professionals_id_foreign');
            $table->dropForeign('professional_ratings_products_id_foreign');
            $table->dropIndex('professional_ratings_professionals_id_index');
            $table->dropIndex('professional_ratings_products_id_index');
        });
        Schema::dropIfExists('professional_ratings');
    }
}
