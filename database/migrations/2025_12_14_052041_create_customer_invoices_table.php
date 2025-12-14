<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('delivery_order_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('journal')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'paid'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('set null');
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_invoices');
    }
};