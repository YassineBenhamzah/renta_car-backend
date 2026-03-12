<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand');             // e.g., Toyota
            $table->string('model');             // e.g., Camry
            $table->integer('year');             // e.g., 2023
            $table->string('registration_number')->unique(); // Matrix/Plate Number
            $table->string('color');

            // Technical Specs
            $table->enum('transmission', ['manual', 'automatic']);
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->integer('mileage')->nullable(); // Number of KM
            $table->integer('doors')->default(4);
            $table->integer('passengers')->default(5);

            // Pricing & Status
            $table->decimal('price_per_day', 8, 2);
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');

            // Media & Extras
            $table->text('details')->nullable(); // Additional description
            $table->json('features')->nullable(); // AC, GPS, Bluetooth (stored as JSON)
            $table->string('image')->nullable();  // Main image output path

            // Ownership
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
