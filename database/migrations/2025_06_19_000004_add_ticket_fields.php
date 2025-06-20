<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // First drop the foreign key constraint if it exists
            $table->dropForeign(['technician_id']);
            // Then drop the column
            $table->dropColumn('technician_id');
            // Add new columns
            $table->unsignedBigInteger('assigned_to')->nullable()->after('user_id');
            $table->datetime('resolved_at')->nullable()->after('priority');
            $table->datetime('due_date')->nullable()->after('resolved_at');
            $table->json('attachments')->nullable()->after('due_date');
            $table->softDeletes();
            
            // Add new foreign key and indexes
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'priority']);
            $table->index('assigned_to');
            $table->index('due_date');
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