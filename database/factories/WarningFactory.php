<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class WarningFactory.
 *
 * @author annejan@badge.team
 */
class WarningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warning::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id'  => Project::factory(),
            'description' => $this->faker->paragraph,
        ];
    }
}
