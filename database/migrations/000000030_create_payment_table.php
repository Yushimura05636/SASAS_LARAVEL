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
        Schema::create('payment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->dateTime('prepared_at');
            $table->string('document_status_code');
            $table->unsignedBigInteger('prepared_by_id');
            $table->decimal('amount_paid', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps(); #This is the substitue of created_at field and updated_at

            $table->foreign('customer_id')->references('id')->on('customer');
            $table->foreign('prepared_by_id')->references('id')->on('user_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment');
    }
};
