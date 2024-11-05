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
        Schema::create('loan_application_comaker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_application_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('encoding_order')->nullable();
            $table->timestamps();

            $table->foreign('loan_application_id')->references('id')->on('loan_application')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_application_comaker');
    }
};
