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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method')->nullable(); // visa, mada, apple_pay
            $table->string('gateway'); // paymob, myfatoorah
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index('gateway_transaction_id');
            $table->index('gateway_payment_id');
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
