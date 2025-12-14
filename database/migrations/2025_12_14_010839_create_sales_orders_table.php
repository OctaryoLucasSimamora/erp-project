<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->date('order_date');
            $table->date('confirmation_date')->nullable();
            $table->date('commitment_date')->nullable(); // Delivery date
            $table->date('expiration_date');
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->string('pricelist')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('incoterms')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('terms_and_conditions')->nullable();
            $table->text('payment_terms')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['quotation', 'sales_order', 'locked'])->default('quotation');
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('salesperson_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};