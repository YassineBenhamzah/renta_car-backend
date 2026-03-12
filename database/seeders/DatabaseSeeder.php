<?php

namespace Database\Seeders;

use App\Models\Car;
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
        // 1. Create an Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rentacar.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // password is 'password'
                'role' => 'admin',
                'phone' => '1234567890',
            ]
        );
        // 2. Create the Car (Toyota Corolla)
        Car::create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2024,
            'registration_number' => 'ABC-123',
            'color' => 'White',
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'price_per_day' => 50.00,
            'user_id' => $admin->id
        ]);
        // 3. Create a Rental Agent
        User::firstOrCreate(
            ['email' => 'agent@rentacar.com'],
            [
                'name' => 'Agent Smith',
                'password' => bcrypt('password'),
                'role' => 'agent'
            ]
        );
        // 4. Create a Regular Customer
        User::firstOrCreate(
            ['email' => 'client@rentacar.com'],
            [
                'name' => 'John Client',
                'password' => bcrypt('password'),
                'role' => 'user'
            ]
        );
        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Car has been added.');
    }

}
