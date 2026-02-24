<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SimpleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear usuarios
        DB::table('user_asesores')->insert([
            [
                'dni' => '75698021',
                'full_name' => 'Asesor Principal',
                'email' => 'asesor@finanzas.com',
                'password_hash' => Hash::make('prueba123'),
                'phone' => '987654321',
                'role' => 'asesor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '87654321',
                'full_name' => 'Maria Garcia',
                'email' => 'maria@finanzas.com',
                'password_hash' => Hash::make('asesor123'),
                'phone' => '987654322',
                'role' => 'asesor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dni' => '12345678',
                'full_name' => 'Administrador',
                'email' => 'admin@finanzas.com',
                'password_hash' => Hash::make('admin123'),
                'phone' => '999888777',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Crear estados de préstamos
        DB::table('loan_statuses')->insert([
            ['code' => 'active', 'description' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'paid', 'description' => 'Pagado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pending', 'description' => 'Pendiente', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'overdue', 'description' => 'Vencido', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Crear clientes
        DB::table('clients')->insert([
            [
                'full_name' => 'Juan Perez',
                'dni' => '12345678',
                'email' => 'juan@cliente.com',
                'phone' => '555123456',
                'address' => 'Dirección del cliente 1',
                'advisor_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Maria Rodriguez',
                'dni' => '87654321',
                'email' => 'maria@cliente.com',
                'phone' => '555123457',
                'address' => 'Dirección del cliente 2',
                'advisor_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Carlos Lopez',
                'dni' => '98765432',
                'email' => 'carlos@cliente.com',
                'phone' => '555123458',
                'address' => 'Dirección del cliente 3',
                'advisor_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Crear préstamos
        DB::table('loans')->insert([
            [
                'client_id' => 1,
                'advisor_id' => 1,
                'status_id' => 1,
                'amount' => 1000.00,
                'interest_rate' => 10.00,
                'term_months' => 12,
                'monthly_payment' => 91.67,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'notes' => 'Préstamo personal',
                'codigo' => 'P001',
                'tipo_credito' => 'personal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 2,
                'advisor_id' => 1,
                'status_id' => 1,
                'amount' => 2500.00,
                'interest_rate' => 12.00,
                'term_months' => 24,
                'monthly_payment' => 125.00,
                'start_date' => '2024-02-01',
                'end_date' => '2026-01-31',
                'notes' => 'Préstamo para negocio',
                'codigo' => 'P002',
                'tipo_credito' => 'negocio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. Crear ahorros
        DB::table('savings')->insert([
            [
                'client_id' => 1,
                'amount' => 500.00,
                'daily_contribution' => 10.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'status' => 'active',
                'currency' => 'PEN',
                'codigo' => 'A001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 2,
                'amount' => 1000.00,
                'daily_contribution' => 20.00,
                'start_date' => '2024-02-01',
                'end_date' => '2025-01-31',
                'status' => 'active',
                'currency' => 'PEN',
                'codigo' => 'A002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 6. Crear cuotas
        DB::table('installments')->insert([
            [
                'loan_id' => 1,
                'installment_number' => 1,
                'due_date' => '2024-02-01',
                'amount' => 91.67,
                'paid_amount' => 91.67,
                'status' => 'paid',
                'payment_date' => '2024-02-01',
                'payment_method' => 'cash',
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'loan_id' => 1,
                'installment_number' => 2,
                'due_date' => '2024-03-01',
                'amount' => 91.67,
                'paid_amount' => 0.00,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 7. Crear pagos
        DB::table('payments')->insert([
            [
                'loan_id' => 1,
                'amount' => 91.67,
                'payment_method' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Base de datos poblada exitosamente');
        $this->command->info('📋 Usuarios creados:');
        $this->command->info('👤 Asesor: DNI=75698021, Password=prueba123');
        $this->command->info('👤 Asesor: DNI=87654321, Password=asesor123');
        $this->command->info('👑 Admin: DNI=12345678, Password=admin123');
        $this->command->info('🌐 Login: http://127.0.0.1:8000/login');
    }
}
