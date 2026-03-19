<?php

namespace Database\Seeders;

use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea un usuario administrador inicial para poder gestionar el sistema.
     */
    public function run(): void
    {
        // Verificar si ya existe un usuario admin
        $adminExists = UserModel::where('role', 'admin')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists. Skipping...');
            return;
        }

        // Crear usuario administrador
        $admin = UserModel::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // Cambiar en producción
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
        $this->command->warn('IMPORTANT: Change the password in production!');
    }
}
