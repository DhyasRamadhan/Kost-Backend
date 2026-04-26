<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalContract extends Model
{
    protected $table = 'rental_contracts';

    protected $fillable = [
        'tenant_id',
        'room_id',
        'owner_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'status'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'contract_id');
    }
}
