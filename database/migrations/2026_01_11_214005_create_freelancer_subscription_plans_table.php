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
        Schema::create('freelancer_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_name'); // e.g. 'basic', 'bronze', 'silver', 'gold', 'platinum', 'diamond'.
            $table->integer('duration_in_days')->nullable(); //0, 7, 30, 90, 180, 365 days
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency')->default('aud');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('payment_intent');
            // $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelancer_subscription_plans');
    }
};
