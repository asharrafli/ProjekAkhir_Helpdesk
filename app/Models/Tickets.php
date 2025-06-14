<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'technician_id',
        'category_id',
        'title_ticket',
        'description_ticket',
        'status',
        'priority'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function technician(){
        return $this->belongsTo(User::class,'technician_id');
    }
    public function category(){
        return $this->belongsTo(TicketCategory::class,'category_id');
    }
    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            $model->ticket_number = 'SLX'.date('Ymd').'-'.str_pad((Tickets::count() + 1), 4, '0', STR_PAD_LEFT);
        });
    }
}
