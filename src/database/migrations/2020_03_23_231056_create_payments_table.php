<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('payments');
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('orders_id')->index('payments_orders_id_index');
            $table->decimal('Amount', 12, 2)->nullable();
            $table->string('BankCode')->nullable();
            $table->string('BankTranNo')->nullable();
            $table->string('CardType')->nullable();
            $table->text('OrderInfo')->nullable();
            $table->string('PayDate')->nullable();
            $table->integer('ResponseCode')->nullable();
            $table->string('TmnCode')->nullable();
            $table->string('TransactionNo')->nullable();
            $table->string('TxnRef')->nullable();
            $table->string('SecureHashType')->nullable();
            $table->text('SecureHash')->nullable();
            $table->integer('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('orders_id', 'payments_orders_id_foreign')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_orders_id_foreign');
            $table->dropIndex('payments_orders_id_index');
        });
        Schema::dropIfExists('payments');
    }
}
