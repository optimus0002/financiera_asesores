<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AddClient extends Command
{
    protected $signature = 'client:add {advisor_id} {dni} {full_name} {email}';
    protected $description = 'Agregar un cliente a un asesor';

    public function handle(): int
    {
        $advisorId = $this->argument('advisor_id');
        $dni = $this->argument('dni');
        $full_name = $this->argument('full_name');
        $email = $this->argument('email');
        
        try {
            // Insertar cliente en Supabase
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
                'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->post(env('SUPABASE_URL') . '/rest/v1/clients', [
                'advisor_id' => $advisorId,
                'dni' => $dni,
                'full_name' => $full_name,
                'email' => $email,
                'phone' => '555123456',
                'address' => 'Dirección del cliente'
            ]);

            if ($response->successful()) {
                $this->info("✅ Cliente agregado exitosamente");
                $this->info("📋 Asesor ID: {$advisorId}");
                $this->info("👤 Cliente: {$full_name}");
                $this->info("📧 DNI: {$dni}");
                $this->info("📧 Email: {$email}");
                $this->info("🌐 Puedes buscarlo en el dashboard");
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
