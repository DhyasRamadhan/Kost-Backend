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
        Schema::create('electricity_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->integer('token_amount')->nullable();
            $table->decimal('meter_start', 10, 2)->nullable();
            $table->decimal('meter_end', 10, 2)->nullable();
            $table->decimal('usage_kwh', 10, 2)->nullable();
            $table->decimal('estimate_bill', 12, 2)->nullable();
            $table->date('usage_date');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_usages');
    }
};
