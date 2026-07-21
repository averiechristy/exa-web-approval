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
            // Mengubah kolom agar boleh kosong (nullable)
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('due_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            // Mengembalikan kolom menjadi wajib diisi (NOT NULL) jika di-rollback
            $table->timestamp('started_at')->nullable(false)->change();
            $table->timestamp('due_at')->nullable(false)->change();
        });
    }
};