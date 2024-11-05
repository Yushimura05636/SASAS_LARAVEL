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
        Schema::create('factor_rate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_frequency_id');
            $table->unsignedBigInteger('payment_duration_id');
            $table->string('description');
            $table->decimal('value', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            #constraints
            $table->foreign('payment_frequency_id')->references('id')->on('payment_frequency')->onDelete('cascade');
            $table->foreign('payment_duration_id')->references('id')->on('payment_duration')->onDelete('cascade');

            $table->unique('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factor_rate');
    }
};
