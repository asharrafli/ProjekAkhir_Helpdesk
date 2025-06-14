<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = [
        'name'
    ];
    public function tickets(){
        return $this->hasMany(Tickets::class, 'category_id');
    }
}
