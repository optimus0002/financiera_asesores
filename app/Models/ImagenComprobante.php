<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenComprobante extends Model
{
    use HasFactory;

    protected $table = 'imagenes_comprobantes';

    public $timestamps = false; // Deshabilitar timestamps automáticos

    protected $fillable = [
        'id_cuenta',
        'tipo_cuenta',
        'imagen',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relaciones
    public function installment()
    {
        return $this->belongsTo(Installment::class, 'id_cuenta', 'id')
                    ->where('tipo_cuenta', 'cuenta_credito');
    }

    public function savingsInstallment()
    {
        return $this->belongsTo(SavingsInstallment::class, 'id_cuenta', 'id')
                    ->where('tipo_cuenta', 'cuenta_ahorro');
    }

    // Scopes
    public function scopeCredito($query)
    {
        return $query->where('tipo_cuenta', 'cuenta_credito');
    }

    public function scopeAhorro($query)
    {
        return $query->where('tipo_cuenta', 'cuenta_ahorro');
    }
}
