<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('coupon_type')->default(0);
            $table->smallInteger('simple_action')->default(1);
            $table->decimal('discount_amount', 12, 2);
            $table->date('expice_date')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('is_active');
            $table->integer('discount_qty')->default(10000)->nullable();
            $table->longText('product_ids')->nullable();
            $table->longText('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
