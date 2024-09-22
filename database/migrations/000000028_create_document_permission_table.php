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
        Schema::create('document_permission', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_map_code');
            $table->unsignedBigInteger('document_permission');
            $table->dateTime('datetime_granted');
            $table->timestamps();

            #constrains
            $table->foreign('user_id')->references('id')->on('user_account');
            $table->foreign('document_map_code')->references('id')->on('document_map');
            $table->foreign('document_permission')->references('id')->on('document_permission_map');

            // Unique constraint with a shorter name
            //$table->unique(['user_id', 'document_map_code', 'document_permission'], 'unique_doc_perm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_permission');
    }
};
