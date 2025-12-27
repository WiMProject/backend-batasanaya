<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $userRole = DB::table('roles')->where('name', 'user')->first();

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'full_name' => 'Admin Batasanaya',
            'email' => 'admin@mail.com',
            'phone_number' => '081234567890',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'id' => Str::uuid(),
            'full_name' => 'User Test',
            'email' => 'user@mail.com',
            'phone_number' => '081234567891',
            'password' => Hash::make('user123'),
            'role_id' => $userRole->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
