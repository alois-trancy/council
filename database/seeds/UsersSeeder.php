<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        factory(User::class)
            ->create([
                'name' => 'JohnDoe',
                'email' => 'johndoe@example.com',
                'password' => bcrypt('password'),
                'confirmed' => true
            ]);
    }
}
