<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Database\Factories;

use App\Models\Project;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class WarningFactory.
 *
 * @author annejan@badge.team
 * @extends Factory<Warning>
 */
class WarningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Warning>
     */
    protected $model = Warning::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, ProjectFactory|string>
     */
    public function definition()
    {
        return [
            'project_id'  => Project::factory(),
            'description' => $this->faker->paragraph,
        ];
    }
}
