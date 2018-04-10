<?php

use Illuminate\Database\Seeder;

class FirstUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => str_random(8),
            'email' => str_random(12).'@gmail.com',
            'password' => bcrypt('testchat'),
            'type' => ('admin'),
            'isbaned' => ('false'),
            'ismuted' => ('false'),
            ]);
    }
}
