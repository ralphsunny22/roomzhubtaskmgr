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
        Schema::create('task_offers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('task_id')->nullable();
            $table->float('amount_offered_by_freelancer')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('freelancer_id')->nullable();
            $table->string('freelancer_date_availability')->nullable();
            $table->string('freelancer_start_time_available')->nullable();
            $table->string('freelancer_end_time_available')->nullable();
            $table->longText('freelancer_proposal')->nullable();

            $table->string('status')->default('pending'); //pending, accepted(client has paid), declined(done by client), cancelled(done by freelancer),
            //in_progress, completed, failed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_offers');
    }
};
