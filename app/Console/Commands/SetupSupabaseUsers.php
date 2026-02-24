<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;

class SetupSupabaseUsers extends Command
{
    protected $signature = 'supabase:setup-users';
    protected $description = 'Crear usuarios de prueba en Supabase';

    public function handle(SupabaseService $supabase): int
    {
        $this->info('Configurando usuarios en Supabase...');

        try {
            // Usuario asesor
            $supabase->insert('users', [
                'id' => 1,
                'email' => 'asesor@prisma.com',
                'password_hash' => '$2y$12$TARcc1hTmVyuU.AAzDP5QOGD9vta4mXrTk97U7HdFsWB5cyfs9xp6',
                'full_name' => 'Asesor',
                'dni' => '75698021',
                'phone' => '987654321',
                'role' => 'asesor',
                'direccion' => 'Calle Patahuasi',
                'created_at' => '2026-02-16 18:21:18',
                'updated_at' => '2026-02-16 18:21:18'
            ]);

            // Usuario admin
            $supabase->insert('users', [
                'id' => 2,
                'email' => 'admin@prisma.com',
                'password_hash' => '$2y$12$TARcc1hTmVyuU.AAzDP5QOGD9vta4mXrTk97U7HdFsWB5cyfs9xp6',
                'full_name' => 'Administrador',
                'dni' => '12345678',
                'phone' => '999888777',
                'role' => 'admin',
                'direccion' => 'Oficina Principal',
                'created_at' => '2026-02-16 18:21:18',
                'updated_at' => '2026-02-16 18:21:18'
            ]);

            // Cliente de prueba
            $supabase->insert('clients', [
                'id' => 1,
                'full_name' => 'Cliente Prueba',
                'dni' => '87654321',
                'email' => 'cliente@prueba.com',
                'phone' => '555123456',
                'address' => 'Dirección del cliente',
                'advisor_id' => 1,
                'created_at' => '2026-02-16 18:21:18',
                'updated_at' => '2026-02-16 18:21:18'
            ]);

            // Estados de préstamos
            $supabase->insert('loan_statuses', [
                ['id' => 1, 'code' => 'active', 'description' => 'Activo'],
                ['id' => 2, 'code' => 'paid', 'description' => 'Pagado'],
                ['id' => 3, 'code' => 'pending', 'description' => 'Pendiente']
            ]);

            // Préstamo de prueba
            $supabase->insert('loans', [
                'id' => 1,
                'client_id' => 1,
                'amount' => 1000.00,
                'interest_rate' => 10.00,
                'term_months' => 12,
                'monthly_payment' => 91.67,
                'status_id' => 1,
                'start_date' => '2026-02-01',
                'end_date' => '2027-01-31',
                'codigo' => 'P001',
                'created_at' => '2026-02-16 18:21:18',
                'updated_at' => '2026-02-16 18:21:18'
            ]);

            $this->info('✅ Usuarios creados exitosamente en Supabase');
            $this->info('');
            $this->info('🔑 Credenciales de prueba:');
            $this->info('📋 Asesor: DNI=75698021, Password=prueba123');
            $this->info('👑 Admin: DNI=12345678, Password=admin123');
            $this->info('');
            $this->info('🌐 Accede a: http://127.0.0.1:8000/login');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error al crear usuarios: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
