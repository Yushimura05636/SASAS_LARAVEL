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
        Schema::create('user_account_status', function (Blueprint $table) {
            $table->bigIncrements('id'); // Or $table->unsignedBigInteger('id') if not using bigIncrements
            $table->string('description');

            #constraints
            $table->unique('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_account_status');
    }
};
