<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->string('department_name');
            $table->string('company');
            $table->string('job_location')->nullable();
            $table->integer('expected_new_employees')->default(0);
            $table->text('job_description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_positions');
    }
};