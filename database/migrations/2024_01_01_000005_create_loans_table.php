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
        Schema::create('loans', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('advisor_id');
            $table->unsignedBigInteger('status_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->decimal('monthly_payment', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->string('codigo')->unique();
            $table->string('tipo_credito')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('advisor_id')->references('id')->on('user_asesores')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('loan_statuses')->onDelete('cascade');
            
            $table->index('client_id');
            $table->index('advisor_id');
            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
