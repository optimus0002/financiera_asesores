<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;

class CheckUsers extends Command
{
    protected $signature = 'users:check';
    protected $description = 'Verificar usuarios existentes en Supabase';

    public function handle(SupabaseService $supabase): int
    {
        try {
            $this->info('Verificando usuarios en Supabase...');
            
            // Obtener todos los usuarios
            $users = $supabase->from('users')
                ->select('id, dni, full_name, email, role, password_hash')
                ->get();
            
            if (empty($users)) {
                $this->error('❌ No se encontraron usuarios');
                return Command::FAILURE;
            }
            
            $this->info('');
            $this->info('📋 Usuarios encontrados:');
            $this->info('');
            
            foreach ($users as $index => $user) {
                $this->info("👤 ID: {$user['id']}");
                $this->info("📋 DNI: {$user['dni']}");
                $this->info("📧 Email: {$user['email']}");
                $this->info("👑 Nombre: {$user['full_name']}");
                $this->info("🔐 Rol: {$user['role']}");
                $this->info("🔑 Hash: " . substr($user['password_hash'], 0, 30) . "...");
                $this->info('');
                
                // Verificar password prueba123
                $isCorrect = password_verify('prueba123', $user['password_hash']);
                $status = $isCorrect ? '✅ CORRECTO' : '❌ INCORRECTO';
                $this->info("DNI {$user['dni']}: {$status}");
            }
            
            $this->info('');
            $this->info('🌐 Para probar login usa:');
            $this->info('http://127.0.0.1:8000/login');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
