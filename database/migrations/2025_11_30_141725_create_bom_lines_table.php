<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
   Schema::create('bom_lines', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('bom_id');
    $table->unsignedBigInteger('raw_material_id');
    $table->float('quantity');
    $table->decimal('cost', 12, 2);
    $table->decimal('subtotal', 12, 2);
    $table->timestamps();

    $table->foreign('bom_id')->references('id')->on('boms')->onDelete('cascade');
    $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
});

}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_lines');
    }
};
