<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_application', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('group_id');
            //$table->dateTime('datetime_created'); redundant similar function to timestamps();
            $table->dateTime('datetime_prepared')->nullable();
            $table->string('document_status_code');
            //$table->dateTime('datetime_last_updated'); this one also;
            $table->string('loan_application_no');
            $table->decimal('amount_loan', 20, 2);
            $table->decimal('factor_rate', 5, 2);
            $table->decimal('amount_interest', 20, 2);
            $table->decimal('amount_paid', 20, 2);
            $table->dateTime('datetime_target_release')->nullable();
            $table->dateTime('datetime_fully_paid')->nullable();
            $table->dateTime('datetime_approved')->nullable();
            $table->unsignedBigInteger('payment_frequency_id');
            $table->unsignedBigInteger('payment_duration_id');
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->unsignedBigInteger('prepared_by_id')->nullable();
            $table->unsignedBigInteger('released_by_id')->nullable();
            $table->unsignedBigInteger('last_modified_by_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customer');
            $table->foreign('group_id')->references('id')->on('customer_group');
            $table->foreign('payment_frequency_id')->references('id')->on('payment_frequency');
            $table->foreign('payment_duration_id')->references('id')->on('payment_duration');
            $table->foreign('approved_by_id')->references('id')->on('user_account');
            $table->foreign('prepared_by_id')->references('id')->on('user_account');
            $table->foreign('released_by_id')->references('id')->on('user_account');
            $table->foreign('last_modified_by_id')->references('id')->on('user_account');

            #unique constrain
            $table->unique('loan_application_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_application');
    }
};
