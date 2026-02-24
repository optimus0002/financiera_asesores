<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'notes',
        'payment_date',
        'payment_proof',
        'payment_method',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function savings()
    {
        return $this->belongsTo(Savings::class, 'savings_id');
    }

    // Scopes
    public function scopeBySavings($query, $savingsId)
    {
        return $query->where('savings_id', $savingsId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                   ->where('due_date', '<', now());
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('status', 'pending')
                   ->where('due_date', '<=', now()->addDays($days));
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date < now());
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return number_format($this->paid_amount, 2);
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return number_format($this->remaining_amount, 2);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'green',
            'pending' => 'yellow',
            'overdue' => 'red',
            default => 'gray'
        };
    }
}
