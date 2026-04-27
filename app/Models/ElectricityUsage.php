<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectricityUsage extends Model
{
    protected $fillable = [
        'room_id',
        'owner_id',
        'token_amount',
        'meter_start',
        'meter_end',
        'usage_kwh',
        'estimate_bill',
        'usage_date',
    ];

    protected $casts = [
        'token_amount' => 'integer',
        'meter_start' => 'decimal:2',
        'meter_end' => 'decimal:2',
        'usage_kwh' => 'decimal:2',
        'estimate_bill' => 'decimal:2',
        'usage_date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
