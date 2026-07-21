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
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->boolean('flag_open')
                ->default(false);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('flag_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('flag_open')
                ->default(false);
        });

        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropColumn('flag_open');
        });
    }
};