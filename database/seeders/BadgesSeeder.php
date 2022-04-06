<?php

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Badge::count() === 0) {
            foreach ([
                'sha2017' => 'SHA2017 Badge',
                'disobey2019' => 'Disobey 2019',
                'hackerhotel2019' => 'Hacker Hotel 2019',
            ] as $slug => $name) {
                Badge::create(['slug' => $slug, 'name' => $name]);
            }
        }
    }
}
