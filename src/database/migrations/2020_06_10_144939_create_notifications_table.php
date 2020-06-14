<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customers_id')->index('notifications_customers_id_index');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('customers_id', 'notifications_customers_id_foreign')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_customers_id_foreign');
            $table->dropIndex('notifications_customers_id_index');
        });
        Schema::dropIfExists('notifications');
    }
}
