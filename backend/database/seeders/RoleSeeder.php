<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['role' => 'lawyer', 'created_at' => now(), 'updated_at' => now()],
            ['role' => 'staff', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
