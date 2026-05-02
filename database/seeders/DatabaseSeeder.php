<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@philo.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '08123456789',
        ]);

        $this->call(SettingSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(StepSeeder::class);
        $this->call(FeatureSeeder::class);
        $this->call(DummyDataSeeder::class);
    }
}
