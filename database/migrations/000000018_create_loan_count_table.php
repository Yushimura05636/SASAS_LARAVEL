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
        Schema::create('loan_count', function (Blueprint $table) {
            $table->bigIncrements('id'); #primary key
            $table->unsignedBigInteger('loan_count');
            $table->float('min_amount');
            $table->float('max_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_count');
    }
};
