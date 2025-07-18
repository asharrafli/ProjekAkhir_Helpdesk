<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Only drop technician_id if it exists
            if (Schema::hasColumn('tickets', 'technician_id')) {
                try {
                    $table->dropForeign(['technician_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('technician_id');
            }
            
            // Add new columns only if they don't exist
            // Note: assigned_to column already exists in original table creation
            if (!Schema::hasColumn('tickets', 'resolved_at')) {
                $table->datetime('resolved_at')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tickets', 'due_date')) {
                $table->datetime('due_date')->nullable()->after('resolved_at');
            }
            if (!Schema::hasColumn('tickets', 'attachments')) {
                $table->json('attachments')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('tickets', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Add foreign key and indexes in separate schema call
        Schema::table('tickets', function (Blueprint $table) {
            // Note: assigned_to foreign key already exists in original table creation
            
            // Add indexes with try-catch to avoid duplicate index errors
            try {
                $table->index(['status', 'priority']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('assigned_to');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('due_date');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['due_date']);
            $table->dropColumn(['assigned_to', 'resolved_at', 'due_date', 'attachments']);
            $table->unsignedBigInteger('technician_id')->nullable()->after('user_id');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};