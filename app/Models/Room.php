<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'price',
        'status',
        'owner_id'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contracts()
    {
        return $this->hasMany(RentalContract::class);
    }
}
