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
        Schema::create('loan_release', function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->dateTime('datetime_created'); this one is redundant similar function to timestamps();
            $table->dateTime('datetime_prepared');
            $table->string('passbook_number');
            $table->unsignedBigInteger('loan_application_id');
            $table->unsignedBigInteger('prepared_by_id');
            $table->dateTime('datetime_first_due');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('loan_application_id')->references('id')->on('loan_application')->onDelete('cascade');
            $table->foreign('prepared_by_id')->references('id')->on('user_account')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_release');
    }
};
