<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_positions', function (Blueprint $table) {
            // Tambahin kolom mode
            $table->enum('mode', ['custom', 'fixed', 'standard'])
                  ->nullable()
                  ->after('pos_y_percent')
                  ->comment('custom: drag-drop, fixed: manual, standard: auto-template');
        });
    }

    public function down(): void
    {
        Schema::table('approval_positions', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};