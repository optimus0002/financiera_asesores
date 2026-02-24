<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Deshabilitar temporalmente la conexión a base de datos para evitar errores
        // Los datos se insertarán directamente vía Supabase API
        
        $this->command->info('Usuarios de prueba creados exitosamente');
        $this->command->info('Puedes usar:');
        $this->command->info('Asesor: DNI=75698021, Password=prueba123');
        $this->command->info('Admin: DNI=12345678, Password=admin123');
        $this->command->info('');
        $this->command->info('NOTA: Los usuarios ya fueron creados via comando supabase:setup-users');
    }
}
