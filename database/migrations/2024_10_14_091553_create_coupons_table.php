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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('unique_key')->unique()->nullable(); //random str
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('title')->nullable();
            $table->string('code')->nullable();
            $table->float('amount')->nullable();

            $table->string('type')->nullable(); //fixed, percentage

            $table->timestamp('start_date')->nullable();
            $table->timestamp('expiry_date')->nullable();

            $table->boolean('status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
