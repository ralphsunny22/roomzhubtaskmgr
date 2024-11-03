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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('unique_key')->nullable(); //random str
            $table->string('payment_invoice_id')->nullable(); //10000T

            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('task_offer_id')->nullable();

            $table->string('description')->nullable();

            $table->string('currency')->default('usd');

            $table->float('subtotal')->default(0);
            $table->float('tax')->default(0);

            $table->boolean('has_coupon')->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->float('coupon_value')->default(0);

            $table->float('total')->default(0);

            $table->string('payment_method_type')->default('card');
            $table->string('status')->default('pending'); //success, failed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
