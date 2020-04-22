<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('districts_id')->index('districts_id_wards_index');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('districts_id', 'districts_id_wards_index_foreign')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->dropForeign('districts_id_wards_index_foreign');
            $table->dropForeign('districts_id_wards_index');
        });
        Schema::dropIfExists('wards');
    }
}
