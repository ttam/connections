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
            'email' => \config('admin.email'),
        ], [
            'name' => \config('admin.name'),
            'password' => Hash::make(\config('admin.password')),
            'is_admin' => true,
        ]);

        $this->call([
            PuzzleSeeder::class,
        ]);
    }
}
