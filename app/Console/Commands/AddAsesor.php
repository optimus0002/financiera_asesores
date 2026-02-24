<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class AddAsesor extends Command
{
    protected $signature = 'asesor:add {dni} {full_name} {email} {password}';
    protected $description = 'Agregar un nuevo asesor a Supabase';

    public function handle(): int
    {
        $dni = $this->argument('dni');
        $full_name = $this->argument('full_name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        try {
            // Generar hash del password
            $passwordHash = Hash::make($password);
            
            // Insertar en Supabase
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
                'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=minimal'
            ])->post(env('SUPABASE_URL') . '/rest/v1/users', [
                'dni' => $dni,
                'full_name' => $full_name,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => 'asesor',
                'phone' => '987654321',
                'direccion' => 'Dirección del asesor'
            ]);

            if ($response->successful()) {
                $this->info("✅ Asesor agregado exitosamente");
                $this->info("📋 DNI: {$dni}");
                $this->info("👤 Nombre: {$full_name}");
                $this->info("📧 Email: {$email}");
                $this->info("🔑 Password: {$password}");
                $this->info("🌐 Puedes probar el login: http://127.0.0.1:8000/login");
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
