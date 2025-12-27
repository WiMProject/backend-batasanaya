<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon; 

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();

        DB::table('roles')->insert([
            [
                'id' => Str::uuid(),
                'name' => 'admin',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now(), 
            ],
            [
                'id' => Str::uuid(),
                'name' => 'user',
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now(), 
            ],
        ]);
    }
}