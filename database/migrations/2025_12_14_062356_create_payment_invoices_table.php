<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_payment_id');
            $table->unsignedBigInteger('customer_invoice_id');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_payment_id')->references('id')->on('customer_payments')->onDelete('cascade');
            $table->foreign('customer_invoice_id')->references('id')->on('customer_invoices')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['customer_payment_id', 'customer_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};