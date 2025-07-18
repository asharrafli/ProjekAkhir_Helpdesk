<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('ticket_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('ticket_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('ticket_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            // Drop columns only if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('ticket_categories', 'description')) {
                $columnsToDrop[] = 'description';
            }
            if (Schema::hasColumn('ticket_categories', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (Schema::hasColumn('ticket_categories', 'sort_order')) {
                $columnsToDrop[] = 'sort_order';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};