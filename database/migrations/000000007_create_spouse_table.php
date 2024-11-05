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
        Schema::create('spouse', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('family_name');
            $table->string('first_name');
            $table->string('middle_name');
            $table->unsignedBigInteger('gender_code');
            $table->string('email_address');
            $table->string('cellphone_no');

            #constraints
            $table->unique(['first_name', 'family_name', 'middle_name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spouse');
    }
};
