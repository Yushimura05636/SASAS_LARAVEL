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
        Schema::create('employee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sss_no');
            $table->string('phic_no');
            $table->string('tin_no');
            $table->dateTime('datetime_hired')->nullable();
            $table->dateTime('datetime_resigned')->nullable();
            $table->unsignedBigInteger('personality_id'); #foreign key

            #constraints
            $table->unique('sss_no');
            $table->unique('phic_no');
            $table->unique('tin_no');

            #foreign
            $table->foreign('personality_id')->references('id')->on('personality')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
};
