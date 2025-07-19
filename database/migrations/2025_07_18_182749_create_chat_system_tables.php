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
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type',['customer_support','technician_admin'])->default('customer_support');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['active', 'closed', 'pending'])->default('active');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            // index untuk performance
            $table->index(['type', 'status']);
            $table->index(['technician_id', 'type', 'status']);
            $table->index(['customer_id', 'type', 'status']);
      
        });

        Schema::create('chat_messages', function(Blueprint $table){
            $table->id();
            $table->foreignId('chat_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->enum('sender_type', ['customer', 'technician','admin','super_admin'])->default('customer');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // index untuk performance
            $table->index(['chat_room_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
    }
};
