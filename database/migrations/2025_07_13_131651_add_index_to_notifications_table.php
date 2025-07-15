<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->index(['notifiable_type', 'notifiable_id', 'created_at']);
        $table->index(['created_at']);
    });
}

public function down()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->dropIndex(['notifiable_type', 'notifiable_id', 'created_at']);
        $table->dropIndex(['created_at']);
    });
}
};