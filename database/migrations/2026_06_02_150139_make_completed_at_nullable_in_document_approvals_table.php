<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable(false)->change();
        });
    }
};