<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns exist before modifying
        if (Schema::hasColumn('tickets', 'status')) {
            // Use DB::statement for better enum modification support
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'assigned', 'closed', 'resolved', 'pending', 'escalated') DEFAULT 'open'");
        }
        
        if (Schema::hasColumn('tickets', 'priority')) {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'critical', 'urgent') DEFAULT 'medium'");
        }

        Schema::table('tickets', function (Blueprint $table) {
            // Add new fields for advanced functionality only if they don't exist
            if (!Schema::hasColumn('tickets', 'subcategory_id')) {
                $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('resolved_at');
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
        });

        // Add indexes separately to avoid conflicts
        Schema::table('tickets', function (Blueprint $table) {
            $indexes = [
                ['status', 'created_at'],
                ['priority', 'created_at'],
                ['is_escalated', 'escalated_at'],
                'last_activity_at',
                'subcategory_id'
            ];
            
            foreach ($indexes as $index) {
                try {
                    if (is_array($index)) {
                        $table->index($index);
                    } else {
                        $table->index($index);
                    }
                } catch (\Exception $e) {
                    // Index might already exist, continue
                }
            }
        });

        // Create ticket subcategories table
        if (!Schema::hasTable('ticket_subcategories')) {
            Schema::create('ticket_subcategories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index(['category_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_subcategories');
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['priority', 'created_at']);
            $table->dropIndex(['is_escalated', 'escalated_at']);
            $table->dropIndex(['last_activity_at']);
            $table->dropIndex(['subcategory_id']);
            
            $table->dropColumn([
                'subcategory_id',
                'first_response_at',
                'last_activity_at',
                'response_time_minutes',
                'resolution_notes',
                'is_escalated',
                'escalated_at',
                'sla_data'
            ]);
        });
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                  ->default('medium')
                  ->after('status');
        });
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['open', 'in_progress', 'closed'])
                  ->default('open')
                  ->after('description_ticket');
        });
    }
};