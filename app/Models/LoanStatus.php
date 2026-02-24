<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function loans()
    {
        return $this->hasMany(Loan::class, 'status_id');
    }

    // Scopes
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeActive($query)
    {
        return $query->where('code', 'active');
    }

    public function scopePaid($query)
    {
        return $query->where('code', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('code', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('code', 'overdue');
    }

    // Accessors
    public function getBadgeColorAttribute()
    {
        return match($this->code) {
            'active' => 'green',
            'paid' => 'blue',
            'pending' => 'yellow',
            'overdue' => 'red',
            default => 'gray'
        };
    }

    public function getFormattedDescriptionAttribute()
    {
        return ucfirst($this->description);
    }
}
