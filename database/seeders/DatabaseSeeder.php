<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => \env('ADMIN_EMAIL'),
        ], [
            'name' => \env('ADMIN_NAME'),
            'password' => Hash::make(\env('ADMIN_PASSWORD')),
        ]);

        $this->call([
            PuzzleSeeder::class,
        ]);
    }
}
