<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devolucion_ahorros', function (Blueprint $table) {
            $table->id('id');
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('ahorro_id');
            $table->decimal('monto_principal', 12, 2)->default(0.00);
            $table->decimal('intereses', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->decimal('monto_efectivo', 12, 2)->nullable();
            $table->decimal('monto_yape', 12, 2)->nullable();
            $table->string('comprobante_yape')->nullable();
            $table->date('fecha');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'completada', 'cancelada'])->default('pendiente');
            $table->string('metodo_pago')->nullable();
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamp('fecha_cancelado')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('users');
            $table->foreign('ahorro_id')->references('id')->on('savings');
            
            $table->index('cliente_id');
            $table->index('ahorro_id');
            $table->index('fecha');
            $table->index('estado');
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucion_ahorros');
    }
};
