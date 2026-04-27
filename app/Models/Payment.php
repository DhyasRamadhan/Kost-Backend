<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'contract_id',
        'owner_id',
        'tenant_id',
        'amount',
        'payment_date',
        'status',
        'midtrans_order_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
