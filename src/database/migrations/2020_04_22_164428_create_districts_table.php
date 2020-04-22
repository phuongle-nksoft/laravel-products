<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('provinces_id')->index('provinces_id_districts_index');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('provinces_id', 'provinces_id_districts_index_foreign')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropForeign('provinces_id_districts_index_foreign');
            $table->dropForeign('provinces_id_districts_index');
        });
        Schema::dropIfExists('districts');
    }
}
