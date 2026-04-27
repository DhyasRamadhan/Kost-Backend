<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUpdateRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
