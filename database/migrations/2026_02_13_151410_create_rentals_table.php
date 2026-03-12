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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days'); // Total days
            $table->decimal('total_price', 10, 2);

            // Status: Agent reviews this
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'canceled'])->default('pending');
            $table->foreignId('approver_id')->nullable()->constrained('users'); // The agent who approved it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
