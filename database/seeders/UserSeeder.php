<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Pooja',
            'email' => 'pooja@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
