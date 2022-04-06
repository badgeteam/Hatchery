<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class VersionFactory.
 *
 * @author annejan@badge.team
 */
class VersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Version::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'revision'   => 1,
            'project_id' => Project::factory(),
        ];
    }
}
