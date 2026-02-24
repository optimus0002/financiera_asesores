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
        Schema::create('savings_installments', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('savings_id');
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->text('payment_proof')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();

            $table->foreign('savings_id')->references('id')->on('savings')->onDelete('cascade');
            
            $table->index('savings_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_installments');
    }
};
