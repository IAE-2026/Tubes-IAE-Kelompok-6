<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('location_id');
            $table->string('member_card_id')->nullable();
            $table->timestamp('entry_time');
            $table->timestamp('exit_time')->nullable();
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->decimal('base_rate', 12, 2)->nullable();
            $table->decimal('benefit', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('status')->default('BERLANGSUNG');
            $table->string('payment_method')->nullable();
            $table->string('voucher_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
