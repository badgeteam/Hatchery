<?php

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
        $user = new App\User;
        $user->name = 'Anne Jan';
        $user->email = 'badge@annejan.com';
        $user->password = bcrypt('srsly?');
        $user->save();
    }
}