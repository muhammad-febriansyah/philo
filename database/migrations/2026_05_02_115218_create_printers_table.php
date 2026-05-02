<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 32);
            $table->string('name', 100)->nullable();
            $table->string('connector', 32);
            $table->string('device');
            $table->string('profile', 64)->default('simple');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
