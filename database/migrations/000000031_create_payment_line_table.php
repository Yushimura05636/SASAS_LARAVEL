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
        Schema::create('payment_line', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('payment_schedule_id');
            $table->decimal('balance', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payment');
            $table->foreign('payment_schedule_id')->references('id')->on('payment_schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_line');
    }
};
