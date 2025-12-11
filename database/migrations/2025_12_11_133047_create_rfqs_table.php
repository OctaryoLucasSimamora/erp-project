<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number')->unique();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->date('deadline')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('company')->nullable();
            $table->enum('status', ['draft', 'sent', 'cancelled', 'done'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rfqs');
    }
};