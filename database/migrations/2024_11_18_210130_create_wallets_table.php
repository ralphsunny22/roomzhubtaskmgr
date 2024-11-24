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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // User reference, freelancer recieving the money
            $table->unsignedBigInteger('task_id')->nullable(); //used for earnings
            $table->unsignedBigInteger('task_offer_id')->nullable(); //used for earnings
            $table->enum('type', ['earning', 'payout']); // Transaction type
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->text('description')->nullable(); // Optional description

            $table->string('status')->default('unpaid'); //paid(means freelancer has received money in their bank acct)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
