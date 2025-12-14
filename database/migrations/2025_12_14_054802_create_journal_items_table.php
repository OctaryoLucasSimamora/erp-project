<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_invoice_id');
            $table->string('account_code', 10);
            $table->string('account_name');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('customer_invoice_id')->references('id')->on('customer_invoices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_items');
    }
};