<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Database\Seeders;

use App\Models\Category;
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
        if (Category::count() === 0) {
            foreach (
                [
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
                'Virus',
                ] as $name
            ) {
                Category::create(['name' => $name]);
            }
        }
    }
}
