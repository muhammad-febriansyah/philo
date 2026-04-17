<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE transactions MODIFY qris_string TEXT NULL');
        DB::statement('ALTER TABLE transactions MODIFY payment_url TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE transactions MODIFY qris_string VARCHAR(255) NULL');
        DB::statement('ALTER TABLE transactions MODIFY payment_url VARCHAR(255) NULL');
    }
};
