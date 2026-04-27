<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'owner_id',
        'room_number',
        'price',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
