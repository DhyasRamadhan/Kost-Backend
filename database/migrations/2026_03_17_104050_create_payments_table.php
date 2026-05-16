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
            $table->foreignId('contract_id')->constrained('rental_contracts')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'cancelled'
            ])->default('pending');
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable()->unique();
            $table->string('payment_type')->nullable();
            $table->timestamp('paid_at')->nullable();
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
