<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Test User 1',
            'email' => 'test@gmail.com',
            'password' => bcrypt('password'),
        ]);

        DB::table('users')->insert([
            'name' => 'Test User 2',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        DB::table('users')->insert([
            'name' => 'Test User 3',
            'email' => 'andreas@tollanes.com',
            'password' => bcrypt('password'),
        ]);
    }
}
