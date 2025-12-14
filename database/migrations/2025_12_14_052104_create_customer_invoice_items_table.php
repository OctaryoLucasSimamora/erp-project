<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_invoice_id');
            $table->unsignedBigInteger('sales_order_item_id')->nullable();
            $table->unsignedBigInteger('delivery_order_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_invoice_id')->references('id')->on('customer_invoices')->onDelete('cascade');
            $table->foreign('sales_order_item_id')->references('id')->on('sales_order_items')->onDelete('set null');
            $table->foreign('delivery_order_item_id')->references('id')->on('delivery_order_items')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_invoice_items');
    }
};