<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('membership_type', ['perunggu', 'perak', 'emas', 'platina']);
            $table->enum('status', ['aktif', 'kedaluwarsa'])->default('aktif');
            $table->integer('discount_percent')->default(0);
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
