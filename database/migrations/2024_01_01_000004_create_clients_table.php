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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('advisor_id');
            $table->string('full_name');
            $table->string('dni')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('negocio')->nullable();
            $table->text('zona')->nullable();
            $table->timestamps();

            $table->foreign('advisor_id')->references('id')->on('user_asesores')->onDelete('cascade');
            $table->index('advisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
