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
        Schema::create('user_asesores', function (Blueprint $table) {
            $table->id('id');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('full_name');
            $table->string('dni')->unique();
            $table->string('phone')->nullable();
            $table->string('role');
            $table->string('direccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_asesores');
    }
};
