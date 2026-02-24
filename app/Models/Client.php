<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'dni',
        'email',
        'phone',
        'address',
        'advisor_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'client_id');
    }

    public function savings()
    {
        return $this->hasMany(Savings::class, 'client_id');
    }

    // Scopes
    public function scopeByAdvisor($query, $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('dni', 'like', "%{$term}%")
              ->orWhere('full_name', 'like', "%{$term}%");
        });
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->full_name;
    }
}
