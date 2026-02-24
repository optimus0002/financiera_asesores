<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCashClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'advisor_id',
        'closing_date',
        'total_amount',
        'yape_amount',
        'cash_amount',
        'transfer_method',
        'transfer_proof',
        'notes',
        'status',
        'confirmed_by',
        'confirmed_at',
        'payment_type',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'yape_amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'closing_date' => 'date',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Scopes
    public function scopeByAdvisor($query, $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('closing_date', $date);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('closing_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedYapeAmountAttribute()
    {
        return number_format($this->yape_amount, 2);
    }

    public function getFormattedCashAmountAttribute()
    {
        return number_format($this->cash_amount, 2);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'rejected' => 'Rechazado',
            default => ucfirst($this->status)
        };
    }

    public function getFormattedDateAttribute()
    {
        return $this->closing_date->format('d/m/Y');
    }

    public function getFormattedConfirmedAtAttribute()
    {
        return $this->confirmed_at ? $this->confirmed_at->format('d/m/Y H:i') : null;
    }

    public function getTransferMethodLabelAttribute()
    {
        return match($this->transfer_method) {
            'yape' => 'Yape',
            'plin' => 'Plin',
            'transfer' => 'Transferencia Bancaria',
            'deposit' => 'Depósito',
            default => ucfirst($this->transfer_method)
        };
    }
}
