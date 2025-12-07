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
       Schema::create('manufacturing_order_lines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('mo_id')->constrained('manufacturing_orders');
    $table->foreignId('raw_material_id')->constrained('raw_materials');
    $table->integer('qty_required');
    $table->integer('qty_consumed')->default(0);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_lines');
    }
};
