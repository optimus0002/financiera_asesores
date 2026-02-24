<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordPatch extends Command
{
    protected $signature = 'user:update-patch {dni}';
    protected $description = 'Actualizar password con PATCH';

    public function handle(): int
    {
        $dni = $this->argument('dni');
        
        try {
            // Generar hash correcto para "prueba123"
            $correctHash = Hash::make('prueba123');
            
            // Usar PATCH para actualizar solo el password_hash
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
                'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->patch(env('SUPABASE_URL') . '/rest/v1/users?dni=eq.' . $dni, [
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
