<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()->after('package_id')->constrained('vouchers')->nullOnDelete();
            }

            if (! Schema::hasColumn('transactions', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('amount');
            }

            if (! Schema::hasColumn('transactions', 'original_amount')) {
                $table->decimal('original_amount', 12, 2)->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'voucher_id')) {
                $table->dropForeign(['voucher_id']);
                $table->dropColumn('voucher_id');
            }

            if (Schema::hasColumn('transactions', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('transactions', 'original_amount')) {
                $table->dropColumn('original_amount');
            }
        });
    }
};
