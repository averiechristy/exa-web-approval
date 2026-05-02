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
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('document_id')->constrained('documents');
            $table->bigInteger('division_id')->constrained('divisions');
            $table->bigInteger('approver_id')->constrained('users');
            $table->integer('approver_order');
            $table->boolean('show_on_doc');
            $table->string('status', 20);
            $table->text('remarks');
            $table->integer('sla_days');
            $table->dateTime('started_at');
            $table->dateTime('due_at');
            $table->dateTime('completed_at');
            $table->boolean('is_overdue');
            $table->integer('tier');
            $table->bigInteger('workflow_step_id')->constrained('workflow_steps');
            $table->baseColumns();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approvals');
    }
};
