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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->longText('fcm_device_token')->nullable();
            $table->rememberToken();
            $table->longText('user_remember_token')->nullable(); //will be used to check credentials while navigating diff apps/sites from roomzhub
            $table->string('signin_type')->default('email');

            $table->boolean('is_client')->default(false); //created tasks before
            $table->boolean('is_freelancer')->default(false); //has skills to to task

            $table->string('phone_number')->nullable();
            $table->boolean('is_phone_number_visible')->default(false);
            $table->longText('about')->nullable();
            $table->longText('skills')->nullable();

            $table->string('current_latitude')->nullable();
            $table->string('current_longitude')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_country')->nullable();
            $table->string('current_address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
