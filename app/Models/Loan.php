<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'advisor_id',
        'status_id',
        'amount',
        'interest_rate',
        'term_months',
        'monthly_payment',
        'start_date',
        'end_date',
        'notes',
        'codigo',
        'tipo_credito',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
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

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function status()
    {
        return $this->belongsTo(LoanStatus::class, 'status_id');
    }

    public function loanStatus()
    {
        return $this->belongsTo(LoanStatus::class, 'status_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class, 'loan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'loan_id');
    }

    // Scopes
    public function scopeByAdvisor($query, $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query)
    {
        return $query->where('status_id', 1); // Asumiendo que 1 = active
    }

    // Accessors
    public function getNextInstallmentAttribute()
    {
        return $this->installments()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedMonthlyPaymentAttribute()
    {
        return number_format($this->monthly_payment, 2);
    }
}
