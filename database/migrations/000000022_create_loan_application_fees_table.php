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
        Schema::create('loan_application_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_application_id');
            $table->unsignedBigInteger('fee_id');
            $table->decimal('amount', 8, 2);
            $table->string('encoding_order')->nullable();
            $table->timestamps();

            $table->foreign('loan_application_id')->references('id')->on('loan_application')->onDelete('cascade');
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_application_fees');
    }
};
