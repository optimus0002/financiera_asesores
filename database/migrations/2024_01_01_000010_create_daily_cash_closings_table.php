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
        Schema::create('daily_cash_closings', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('advisor_id');
            $table->date('closing_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('yape_amount', 10, 2)->default(0.00);
            $table->decimal('cash_amount', 10, 2)->default(0.00);
            $table->string('transfer_method')->nullable();
            $table->text('transfer_proof')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamps();

            $table->foreign('advisor_id')->references('id')->on('user_asesores')->onDelete('cascade');
            $table->foreign('confirmed_by')->references('id')->on('user_asesores')->onDelete('set null');
            
            $table->index('advisor_id');
            $table->index('closing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_closings');
    }
};
