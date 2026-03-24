<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the Administrator
        User::updateOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Administrador del Sistema',
            'password' => bcrypt('password'),
            'tipo_usuario' => 'ADMINISTRADOR',
        ]);

        // Create the Technician
        User::updateOrCreate(['email' => 'tecnico@tecnico.com'], [
            'name' => 'Técnico de Soporte',
            'password' => bcrypt('password'),
            'tipo_usuario' => 'TECNICO',
        ]);

        // Create the User
        User::updateOrCreate(['email' => 'usuario@usuario.com'], [
            'name' => 'Usuario de Pruebas',
            'password' => bcrypt('password'),
            'tipo_usuario' => 'USUARIO',
        ]);
    }
}
