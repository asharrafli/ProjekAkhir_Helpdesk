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
        Schema::table('tickets', function (Blueprint $table) {
            // Add missing columns that are in the model but not in migration
            if (!Schema::hasColumn('tickets', 'title')) {
                $table->string('title')->after('subcategory_id')->nullable();
            }
            
            if (!Schema::hasColumn('tickets', 'subcategory_id')) {
                $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('ticket_subcategories');
            }
            
            if (!Schema::hasColumn('tickets', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users');
            }
            
            if (!Schema::hasColumn('tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('priority');
            }
            
            if (!Schema::hasColumn('tickets', 'due_date')) {
                $table->timestamp('due_date')->nullable()->after('resolved_at');
            }
            
            if (!Schema::hasColumn('tickets', 'attachments')) {
                $table->json('attachments')->nullable()->after('due_date');
            }
            
            if (!Schema::hasColumn('tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('attachments');
            }
            
            if (!Schema::hasColumn('tickets', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('first_response_at');
            }
            
            if (!Schema::hasColumn('tickets', 'response_time_minutes')) {
                $table->integer('response_time_minutes')->nullable()->after('last_activity_at');
            }
            
            if (!Schema::hasColumn('tickets', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('response_time_minutes');
            }
            
            if (!Schema::hasColumn('tickets', 'is_escalated')) {
                $table->boolean('is_escalated')->default(false)->after('resolution_notes');
            }
            
            if (!Schema::hasColumn('tickets', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('is_escalated');
            }
            
            if (!Schema::hasColumn('tickets', 'sla_data')) {
                $table->json('sla_data')->nullable()->after('escalated_at');
            }
            
            if (!Schema::hasColumn('tickets', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Update status enum to include all values used in model
            $table->enum('status', ['open', 'in_progress', 'assigned', 'pending', 'escalated', 'closed', 'resolved'])->default('open')->change();
            
            // Update priority enum to include critical
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical'])->default('medium')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'subcategory_id', 
                'assigned_to',
                'resolved_at',
                'due_date',
                'attachments',
                'first_response_at',
                'last_activity_at',
                'response_time_minutes',
                'resolution_notes',
                'is_escalated',
                'escalated_at',
                'sla_data',
                'deleted_at'
            ]);
        });
    }
};