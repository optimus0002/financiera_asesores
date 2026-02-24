<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Savings extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'amount',
        'daily_contribution',
        'start_date',
        'end_date',
        'status',
        'currency',
        'codigo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'daily_contribution' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function savingsInstallments()
    {
        return $this->hasMany(SavingsInstallment::class, 'savings_id');
    }

    // Scopes
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDailyContributionAttribute()
    {
        return number_format($this->daily_contribution, 2);
    }

    public function getCurrencySymbolAttribute()
    {
        return $this->currency === 'USD' ? '$' : 'S/';
    }
}
