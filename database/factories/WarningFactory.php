<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WarningFactory.
 *
 * @extends Factory<Warning>
 * @author annejan@badge.team
 */
class WarningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Warning>
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
