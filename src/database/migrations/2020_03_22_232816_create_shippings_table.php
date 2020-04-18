<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customers_id')->index('shippings_customers_id_index');
            $table->string('address')->nullable();
            $table->string('phone');
            $table->string('name');
            $table->string('company')->nullabel();
            $table->boolean('is_default')->default(0)->nullabel();
            $table->boolean('last_shipping')->default(0)->nullabel();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('customers_id', 'shippings_customers_id_foreign')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropForeign('shippings_customers_id_foreign');
            $table->dropIndex('shippings_customers_id_index');
        });
        Schema::dropIfExists('shippings');
    }
}
