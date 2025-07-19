<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'name',
        'type',
        'customer_id',
        'technician_id',
        'ticket_id',
        'status',
        'last_activity'
    ];

    protected $casts = [
        'last_activity' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
    public function ticket()
    {
        return $this->belongsTo(Tickets::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage(){
        return $this->hasOne(ChatMessage::class)->latest();
    }
    public function unreadMessagesFor($userId){
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
    // Check if user can access this room
    public function canAccess($user)
    {
        // Super Admin can access all rooms
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin can access customer support rooms
        if ($user->isAdmin() && $this->type === 'customer_support') {
            return true;
        }

        // Customer can only access their own room
        if ($this->type === 'customer_support' && $this->customer_id === $user->id) {
            return true;
        }

        // Technician can access technician-admin rooms and their assigned rooms
        if ($user->hasRole('technician')) {
            return $this->technician_id === $user->id || $this->type === 'technician_admin';
        }

        return false;
    }
}
