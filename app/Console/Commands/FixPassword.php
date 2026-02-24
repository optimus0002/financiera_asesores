<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class FixPassword extends Command
{
    protected $signature = 'user:fix-password {dni}';
    protected $description = 'Corregir password usando HTTP directo';

    public function handle(): int
    {
        $dni = $this->argument('dni');
        
        try {
            // Generar hash correcto para "prueba123"
            $correctHash = Hash::make('prueba123');
            
            // Llamada directa a Supabase REST API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
                'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->post(env('SUPABASE_URL') . '/rest/v1/users', [
                'dni' => $dni,
                'password_hash' => $correctHash
            ]);

            if ($response->successful()) {
                $this->info("✅ Password actualizado exitosamente");
                $this->info("📋 DNI: {$dni}");
                $this->info("🔑 Password: prueba123");
                $this->info("🌐 Prueba el login: http://127.0.0.1:8000/login");
                return Command::SUCCESS;
            } else {
                $this->error("❌ Error HTTP: " . $response->status());
                $this->error("Respuesta: " . $response->body());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
