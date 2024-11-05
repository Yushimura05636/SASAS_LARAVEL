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
        Schema::create('personality', function (Blueprint $table) {
            $table->bigIncrements('id'); #primary key
            //$table->dateTime('datetime_created'); redundant to timestamps();
            //$table->dateTime('datetime_last_modified'); thiso one too;
            $table->date('datetime_registered');
            $table->string('family_name');
            $table->string('middle_name');
            $table->string('first_name');
            //$table->string('description'); not needed
            $table->date('birthday');
            $table->string('civil_status');
            $table->string('gender_code');
            $table->string('house_street');
            $table->string('purok_zone');
            $table->string('postal_code');
            $table->string('telephone_no');
            $table->string('email_address');
            $table->string('cellphone_no');

            #foregin key
            $table->unsignedBigInteger('name_type_code');
            $table->unsignedBigInteger('personality_status_code'); #foregin key
            //$table->unsignedBigInteger('branch_id'); #foregin key
            $table->unsignedBigInteger('barangay_id'); #foregin key
            $table->unsignedBigInteger('city_id'); #foregin key
            $table->unsignedBigInteger('country_id'); #foregin key
            $table->unsignedBigInteger('province_id'); #foregin key
            //$table->unsignedBigInteger('spouse_id'); #foregin key; not needed
            $table->unsignedBigInteger('credit_status_id'); #foregin key

            #constraints
            $table->unique(['first_name', 'middle_name', 'family_name']);

            $table->string('notes')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personality');
    }
};
