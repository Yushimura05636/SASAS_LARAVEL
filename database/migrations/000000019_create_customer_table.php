<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    #In this table there are two foreign keys
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')->nullable(); #foregin key
            $table->unsignedBigInteger('passbook_no');
            $table->unsignedBigInteger('loan_count');
            $table->boolean('enable_mortuary')->nullable();
            $table->dateTime('mortuary_coverage_start')->nullable();
            $table->dateTime('mortuary_coverage_end')->nullable();
            $table->unsignedBigInteger('personality_id'); #foreign key

            #constraints
            $table->foreign('group_id')->references('id')->on('customer_group')->onDelete('cascade');
            $table->foreign('personality_id')->references('id')->on('personality')->onDelete('cascade');

            #gi add nako karung nov 18 2024
            $table->unique('passbook_no');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
