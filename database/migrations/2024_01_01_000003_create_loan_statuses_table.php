<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_statuses', function (Blueprint $table) {
            $table->id('id');
            $table->string('code')->unique();
            $table->string('description');
            $table->timestamps();
        });

        // Insertar datos iniciales
        DB::table('loan_statuses')->insert([
            ['code' => 'activo', 'description' => 'Préstamo activo', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'completado', 'description' => 'Préstamo completado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'cancelado', 'description' => 'Préstamo cancelado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'mora', 'description' => 'Préstamo en mora', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pendiente', 'description' => 'Préstamo pendiente de aprobación', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_statuses');
    }
};
