<?php

use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach([
            'Uncategorised',
            'Event related',
            'Games',
            'Graphics',
            'Hardware',
            'Utility',
            'Wearable',
            'Data',
            'Silly',
            'Hacking',
            'Troll',
            'Unusable',
            'Adult',
            'Virus'
                ] as $name) {
            \App\Models\Category::create(['name' => $name]);
        }
    }
}
