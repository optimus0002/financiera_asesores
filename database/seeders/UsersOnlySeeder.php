<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersOnlySeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Insertar usuarios solo con las columnas necesarias
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

            $this->command->info('✅ Usuarios creados exitosamente');
            $this->command->info('📋 Usuarios creados:');
            $this->command->info('👤 Asesor: DNI=75698021, Password=prueba123');
            $this->command->info('👤 Asesor: DNI=87654321, Password=asesor123');
            $this->command->info('👑 Admin: DNI=12345678, Password=admin123');
            $this->command->info('🌐 Login: http://127.0.0.1:8000/login');

        } catch (\Exception $e) {
            $this->command->error('❌ Error al insertar usuarios: ' . $e->getMessage());
        }
    }
}
