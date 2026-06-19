<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type');  // e.g., LocationCreated
            $table->string('reference_id');      // e.g., loc_004
            $table->string('receipt_number');     // IAE-LOG-2026-XXXX from SOAP response
            $table->string('status');             // SUCCESS or FAILED
            $table->text('soap_request')->nullable();
            $table->text('soap_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_receipts');
    }
};
