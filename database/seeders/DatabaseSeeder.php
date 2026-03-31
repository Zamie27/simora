<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Define roles
        $roles = [
            'Manajemen' => 'Administrator with full access',
            'Pelatih' => 'Coach managing athletes',
            'Atlet' => 'Athlete user',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $atletRole = Role::where('name', 'Atlet')->first();
        $pelatihRole = Role::where('name', 'Pelatih')->first();
        $manajemenRole = Role::where('name', 'Manajemen')->first();

        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Manajemen',
                'password' => Hash::make('password'),
                'role_id' => $manajemenRole->id,
            ]
        );

        // Coach Users
        for ($i = 0; $i < 2; $i++) {
            User::firstOrCreate(
                ['email' => 'pelatih'.$i.'@example.com'],
                [
                    'name' => 'Coach Budi '.$i,
                    'password' => Hash::make('password'),
                    'role_id' => $pelatihRole->id,
                ]
            );
        }

        // Athlete Users
        for ($i = 0; $i < 10; $i++) {
            User::firstOrCreate(
                ['email' => 'atlet'.$i.'@example.com'],
                [
                    'name' => 'Atlet Andi '.$i,
                    'password' => Hash::make('password'),
                    'role_id' => $atletRole->id,
                ]
            );
        }

        $this->call(DummyDataSeeder::class);
    }
}
