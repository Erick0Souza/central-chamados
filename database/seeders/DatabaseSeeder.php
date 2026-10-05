<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Acessos e contas', 'Equipamentos', 'Rede e conexão', 'Sistemas', 'Outras solicitações'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
