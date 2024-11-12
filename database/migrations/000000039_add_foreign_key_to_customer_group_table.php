<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('customer_group', function (Blueprint $table) {
            $table->foreign('collector_id')
                ->references('id')
                ->on('user_account')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('customer_group', function (Blueprint $table) {
            $table->dropForeign(['collector_id']);
        });
    }
};
