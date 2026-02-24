<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword extends Command
{
    protected $signature = 'user:update-password {dni} {password}';
    protected $description = 'Actualizar password de usuario en Supabase';

    public function handle(SupabaseService $supabase): int
    {
        $dni = $this->argument('dni');
        $password = $this->argument('password');

        try {
            // Buscar usuario por DNI
            $user = $supabase->from('users')
                ->where('dni', $dni)
                ->first();

            if (!$user) {
                $this->error("Usuario con DNI {$dni} no encontrado");
                return Command::FAILURE;
            }

            // Generar nuevo hash
            $newHash = Hash::make($password);

            // Actualizar usuario
            $updateResult = $supabase->update('users', [
                'password_hash' => $newHash,
                'updated_at' => now()->toDateTimeString()
            ], ['id' => $user['id']]);

            if ($updateResult) {
                $this->info("✅ Password actualizado exitosamente");
                $this->info("📋 Usuario: {$dni}");
                $this->info("🔑 Nuevo Password: {$password}");
                $this->info("🌐 Puedes probar el login");
                return Command::SUCCESS;
            } else {
                $this->error("❌ Error al actualizar password");
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
