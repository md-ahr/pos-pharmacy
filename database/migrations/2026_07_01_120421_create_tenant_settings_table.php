<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('tax_rate', 5, 4)->default('0.1500');
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->foreignId('default_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
