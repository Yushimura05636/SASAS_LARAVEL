<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_schedule', function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->dateTime('datetime_created'); redundant and has similar function to timestamps();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('loan_released_id')->nullable();
            $table->dateTime('datetime_due');
            $table->decimal('amount_due', 20, 13);
            $table->decimal('amount_interest', 20, 13);
            $table->decimal('amount_paid', 20, 13)->default(0);
            $table->string('payment_status_code');
            $table->text('remarks')->nullable();

            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            $table->foreign('loan_released_id')->references('id')->on('loan_release')->onDelete('cascade');
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
        Schema::dropIfExists('payment_schedule');
    }
};
