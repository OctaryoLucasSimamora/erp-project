<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Rename tables jika ada
        if (Schema::hasTable('r_f_q_s')) {
            Schema::rename('r_f_q_s', 'rfqs');
        }
        
        if (Schema::hasTable('r_f_q_lines')) {
            Schema::rename('r_f_q_lines', 'rfq_lines');
        }
        
        if (Schema::hasTable('p_o_lines')) {
            Schema::rename('p_o_lines', 'po_lines');
        }
    }

    public function down()
    {
        // Kembalikan ke nama semula
        if (Schema::hasTable('rfqs')) {
            Schema::rename('rfqs', 'r_f_q_s');
        }
        
        if (Schema::hasTable('rfq_lines')) {
            Schema::rename('rfq_lines', 'r_f_q_lines');
        }
        
        if (Schema::hasTable('po_lines')) {
            Schema::rename('po_lines', 'p_o_lines');
        }
    }
};