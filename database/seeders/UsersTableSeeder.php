<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'ajay',
                'email' => 'ajay@seeder.com',
                'password' => Hash::make('pass1'),
            ],
            [
                'name' => 'Santosh',
                'email' => 'Santosh@seeder.com',
                'password' => Hash::make('pass1'),
            ],
        ]);
        
    }
}
