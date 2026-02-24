<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Hash;

class SetCorrectPassword extends Command
{
    protected $signature = 'user:set-password {dni}';
    protected $description = 'Establecer password correcto para usuario';

    public function handle(SupabaseService $supabase): int
    {
        $dni = $this->argument('dni');
        
        try {
            // Buscar usuario
            $user = $supabase->from('users')
                ->where('dni', $dni)
                ->first();

            if (!$user) {
                $this->error("❌ Usuario con DNI {$dni} no encontrado");
                return Command::FAILURE;
            }

            // Establecer password "prueba123" con hash correcto
            $correctHash = Hash::make('prueba123');

            // Actualizar usuario
            $updateResult = $supabase->update('users', [
                'password_hash' => $correctHash,
                'updated_at' => now()->toDateTimeString()
            ], ['id' => $user['id']]);

            if ($updateResult) {
                $this->info("✅ Password actualizado exitosamente");
                $this->info("📋 Usuario: {$dni}");
                $this->info("🔑 Nuevo Password: prueba123");
                $this->info("🌐 Puedes probar el login ahora");
                $this->info("🌐 URL: http://127.0.0.1:8000/login");
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
