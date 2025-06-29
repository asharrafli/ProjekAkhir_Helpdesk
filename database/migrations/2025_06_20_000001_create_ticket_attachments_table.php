<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ticket_attachments')) {
            Schema::create('ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
                $table->string('original_name');
                $table->string('file_name');
                $table->string('file_path');
                $table->string('mime_type');
                $table->integer('file_size');
                $table->foreignId('uploaded_by')->constrained('users');
                $table->timestamps();
                
                $table->index(['ticket_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};