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
        Schema::create('user_accesses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained('users');
            $table->bigInteger('organization_id')->constrained('organizations');
            $table->bigInteger('division_id')->constrained('divisions');
            $table->bigInteger('role_id')->constrained('roles');
            $table->bigInteger('manager_id')->constrained('users')->nullable();
            $table->baseColumns();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_accesses');
    }
};
