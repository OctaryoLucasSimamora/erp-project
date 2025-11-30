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
    Schema::create('boms', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id'); // product jadi
        $table->float('quantity')->default(1);    // jumlah hasil produksi
        $table->float('total_cost')->default(0);  // otomatis
        $table->timestamps();

        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
