<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tickets extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to',
        'category_id',
        'title_ticket',
        'description_ticket',
        'status',
        'priority',
        'resolved_at',
        'due_date',
        'attachments'
    ];
    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'datetime',
        'attachments' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ticket', 'status', 'priority', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['closed', 'resolved']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ticket_number = 'SLX' . date('Ymd') . '-' . str_pad((Tickets::count() + 1), 4, '0', STR_PAD_LEFT);
        });

        static::updating(function ($model) {
            if ($model->isDirty('status') && in_array($model->status, ['closed', 'resolved'])) {
                $model->resolved_at = now();
            }
        });
    }
}
