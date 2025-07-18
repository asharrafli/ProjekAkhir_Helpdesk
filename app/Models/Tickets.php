<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tickets extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'tickets';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to',
        'category_id',
        'subcategory_id',
        'title',
        'title_ticket',
        'description_ticket',
        'status',
        'priority',
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
    ];
    protected $casts = [
        'resolved_at' => 'datetime',
        'due_date' => 'datetime',
        'attachments' => 'array',
        'first_response_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'is_escalated' => 'boolean',
        'escalated_at' => 'datetime',
        'sla_data' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'assigned_to'])
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

    public function subcategory()
    {
        return $this->belongsTo(TicketSubcategory::class, 'subcategory_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
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

    public function scopeEscalated($query)
    {
        return $query->where('is_escalated', true);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'assigned', 'pending']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['closed', 'resolved']);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    public function scopeRecentActivity($query, $days = 7)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    public function scopeNeedsResponse($query)
    {
        return $query->whereNull('first_response_at')
                    ->whereIn('status', ['open', 'pending']);
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
            
            // Update last activity when any field changes
            $model->last_activity_at = now();
        });
    }

    // Helper methods
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'assigned', 'pending']);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'resolved']);
    }

    public function canBeAssigned(): bool
    {
        return in_array($this->status, ['open', 'pending']);
    }

    public function canBeClaimed(): bool
    {
        return $this->status === 'open' && is_null($this->assigned_to);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'info',
            'in_progress' => 'warning',
            'assigned' => 'primary',
            'pending' => 'secondary',
            'escalated' => 'danger',
            'closed' => 'success',
            'resolved' => 'success',
            default => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    public function getResponseTimeInHours(): ?float
    {
        return $this->response_time_minutes ? round($this->response_time_minutes / 60, 2) : null;
    }

    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    public function getAgeInHours(): int
    {
        return $this->created_at->diffInHours(now());
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->isOpen();
    }

    public function canEdit(): bool
    {
        // A ticket can be edited if:
        // 1. The user is the ticket creator, or
        // 2. The user is the assigned technician, or
        // 3. The user has permission to edit all tickets
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        $userId = $this->getCurrentUserId();
        
        try {
            $hasPermission = $user->can('edit-tickets');
            return $hasPermission || 
                   $this->user_id === $userId || 
                   $this->assigned_to === $userId;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getCurrentUserId()
    {
        if (app()->has('auth') && app('auth')->check()) {
            return app('auth')->id();
        }
        return null;
    }
    
    protected function getCurrentUser()
    {
        if (app()->has('auth') && app('auth')->check()) {
            return app('auth')->user();
        }
        return null;
    }
    public function comments(){
        return $this->hasMany(TicketComment::class, 'ticket_id')->orderBy('created_at','asc');
    }
    public function publicComments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id')->where('is_internal', false)->orderBy('created_at', 'asc');
    }

    public function internalNotes()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id')->where('is_internal', true)->orderBy('created_at', 'asc');
    }
}
