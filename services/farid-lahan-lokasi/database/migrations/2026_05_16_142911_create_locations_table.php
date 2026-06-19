<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('locations', function (Blueprint $table) {
            $table->string('id')->primary(); 
            $table->string('name');
            $table->string('address');
            $table->string('type'); 
            $table->string('parking_type'); 
            $table->integer('total_spots');
            $table->integer('available_spots');
            $table->integer('base_rate'); 
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('locations');
    }
};