<?php

use Illuminate\Database\Seeder;
use Dirape\Token\Token;

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
            'password' => bcrypt('testchat'),
            'admin' => (1),
            'token' => (new Token())->Unique('users', 'token', 40),
            ]);
    }
}
