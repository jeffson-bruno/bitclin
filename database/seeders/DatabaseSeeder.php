<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executa o RoleSeeder primeiro
        $this->call([
            RoleSeeder::class,
        ]);

        // Cria um usuário admin de exemplo
        $admin = User::firstOrCreate(
            ['usuario' => 'admin'], // agora verifica pelo campo único
            [
                'name' => 'Administrador',
                'email' => 'admin@bitclin.com',
                'password' => bcrypt('admin123'),
            ]
        );

        // Atribui o perfil admin a ele
        $admin->assignRole('admin');
        }
}
