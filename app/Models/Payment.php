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

    public function contract()
    {
        return $this->belongsTo(RentalContract::class, 'contract_id');
    }
}
