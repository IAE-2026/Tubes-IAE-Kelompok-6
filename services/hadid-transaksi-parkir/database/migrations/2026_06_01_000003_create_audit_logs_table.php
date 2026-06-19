<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // transaksi sumber (mis. trx_001)
            $table->string('transaction_id')->nullable()->index();
            $table->string('activity_name');
            $table->string('team_id');

            // payload JSON yang dikirim sebagai <LogContent>
            $table->json('log_content');
            // SOAP envelope mentah yang dikirim (Modul 2)
            $table->longText('soap_request')->nullable();
            // respons mentah dari server SOAP dosen
            $table->longText('soap_response')->nullable();

            // hasil parsing respons SOAP
            $table->string('receipt_number')->nullable()->index();
            $table->string('status')->nullable();      // SUCCESS / FAILED / ERROR
            $table->integer('http_status')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
