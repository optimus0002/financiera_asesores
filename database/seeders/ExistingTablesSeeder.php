<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ExistingTablesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear estados de préstamos
        DB::table('loan_statuses')->insert([
            ['code' => 'active', 'description' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'paid', 'description' => 'Pagado', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pending', 'description' => 'Pendiente', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'overdue', 'description' => 'Vencido', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Crear clientes
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

        // 3. Crear préstamos
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

        // 4. Crear ahorros
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

        // 5. Crear cuotas
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

        // 6. Crear pagos
        DB::table('payments')->insert([
            [
                'loan_id' => 1,
                'amount' => 91.67,
                'payment_method' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Tablas pobladas exitosamente (excepto users)');
        $this->command->info('📋 Datos creados:');
        $this->command->info('👤 3 clientes asignados');
        $this->command->info('💰 2 préstamos creados');
        $this->command->info('🏦 2 cuentas de ahorro');
        $this->command->info('📊 2 cuotas de préstamo');
        $this->command->info('💳 1 pago registrado');
        $this->command->info('📋 4 estados de préstamo');
        $this->command->info('⚠️  Necesitas crear usuarios manualmente en la tabla users');
    }
}
