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
            $table->unsignedBigInteger('professionals_id')->index();
            $table->unsignedBigInteger('products_id')->index();
            $table->longText('description')->nullable();
            $table->double('ratings', 8, 2)->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $this->foreign('professionals_id')->references('id')->on('professionals')->onDelete('cascade');
            $this->foreign('products_id')->references('id')->on('products')->onDelete('cascade');
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
            $table->dropForeign(['professionals_id', 'products_id']);
            $table->dropIndex(['professionals_id', 'products_id']);
        });
        Schema::dropIfExists('professional_ratings');
    }
}
