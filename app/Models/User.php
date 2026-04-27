<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'verification_status',
        'verified_at',
        'rejected_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'owner_id');
    }

    public function tenantsManaged()
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    public function tenantsProfile()
    {
        return $this->hasOne(Tenant::class, 'user_id');
    }

    public function contracts()
    {
        return $this->hasMany(RentalContract::class, 'owner_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'owner_id');
    }
}
