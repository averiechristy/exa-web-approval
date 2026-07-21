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
            // Mengubah kolom-kolom ini agar boleh NULL (tidak wajib diisi)
            $table->text('remarks')->nullable()->change();
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
            // Kembalikan ke aturan asal jika migrasi di-rollback
            $table->text('remarks')->nullable(false)->change();
            $table->timestamp('started_at')->nullable(false)->change();
            $table->timestamp('due_at')->nullable(false)->change();
        });
    }
};