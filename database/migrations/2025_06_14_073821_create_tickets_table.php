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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->contrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->string('title_ticket');
            $table->text('description_ticket');
            $table->enum('status', ['open', 'in_progress','closed'])->default('open');
            $table->enum('priority',['low','medium','high','urgent'])->default('medium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
