<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Modul 2 - ReceiptNumber yang dikembalikan server SOAP dosen
            $table->string('audit_receipt_number')->nullable()->after('paid_at');
            $table->string('audit_status')->nullable()->after('audit_receipt_number');
            // Modul 3 - status broadcast event ke RabbitMQ
            $table->string('event_published_status')->nullable()->after('audit_status');
            // user SSO yang mengeksekusi transaksi kritis (Modul 1)
            $table->string('processed_by')->nullable()->after('event_published_status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'audit_receipt_number',
                'audit_status',
                'event_published_status',
                'processed_by',
            ]);
        });
    }
};
