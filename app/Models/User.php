<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user_asesores';

    protected $fillable = [
        'dni',
        'full_name',
        'email',
        'password_hash',
        'phone',
        'role',
        'direccion',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function clients()
    {
        return $this->hasMany(Client::class, 'advisor_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'advisor_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'advisor_id');
    }

    public function dailyCashClosings()
    {
        return $this->hasMany(DailyCashClosing::class, 'advisor_id');
    }

    public function confirmedClosings()
    {
        return $this->hasMany(DailyCashClosing::class, 'confirmed_by');
    }

    // Accessors
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getFullNameAttribute($value)
    {
        return $value;
    }

    // Scopes
    public function scopeAsesor($query)
    {
        return $query->where('role', 'asesor');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }
}
