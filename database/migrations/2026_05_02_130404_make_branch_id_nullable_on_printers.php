<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropUnique(['branch_id', 'purpose']);
        });

        Schema::table('printers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->change();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unique(['branch_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropUnique(['branch_id', 'purpose']);
        });

        Schema::table('printers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable(false)->change();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->unique(['branch_id', 'purpose']);
        });
    }
};
