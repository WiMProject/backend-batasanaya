<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Get admin role
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        
        if (!$adminRole) {
            echo "Admin role not found. Run RoleSeeder first.\n";
            return;
        }

        // Create admin user
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone_number' => '081999888777',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        echo "Admin user created successfully!\n";
        echo "Email: admin@example.com\n";
        echo "Password: admin123\n";
    }
}