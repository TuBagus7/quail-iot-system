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
        // User::factory(10)->create();

        // Kita bikin user Admin buat ngetes login ya Bang!
        \App\Models\User::factory()->create([
            'name' => 'ditky',
            'email' => 'ditky@gmail.com',
            'password' => bcrypt('admin123'), // Password standarnya: admin123
        ]);
    }
}
