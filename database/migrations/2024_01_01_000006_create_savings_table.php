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
        Schema::create('savings', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('daily_contribution', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('currency', 3)->default('PEN');
            $table->string('status');
            $table->string('tipo_ahorro')->nullable();
            $table->integer('periodo')->nullable();
            $table->decimal('tasa', 10, 2)->nullable();
            $table->string('tipo_aportacion')->nullable();
            $table->string('monto_aportacion')->nullable();
            $table->string('codigo')->unique();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('advisor_id')->references('id')->on('user_asesores')->onDelete('set null');
            
            $table->index('client_id');
            $table->index('advisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
