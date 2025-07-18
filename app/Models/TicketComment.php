<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Ticket;
use Spatie\Activitylog\Traits\LogsActivity;

use Spatie\Activitylog\LogOptions;

class TicketComment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'is_internal',
        'is_solution',
    ];
    protected $casts = [
        'is_internal' => 'boolean',
        'is_solution' => 'boolean',
    ];
    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['comment', 'is_internal', 'is_solution'])
            ->setDescriptionForEvent(fn(string $eventName) => "Comment {$eventName}")
            ->logOnlyDirty();
    }
}
