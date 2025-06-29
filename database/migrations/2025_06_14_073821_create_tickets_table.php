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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->foreignId('subcategory_id')->nullable()->constrained('ticket_subcategories');
            $table->string('title');
            $table->string('title_ticket')->nullable(); // Keep for backward compatibility
            $table->text('description_ticket');
            $table->enum('status', ['open', 'in_progress', 'assigned', 'pending', 'escalated', 'closed', 'resolved'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical'])->default('medium');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('response_time_minutes')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->json('sla_data')->nullable();
            $table->softDeletes();
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
