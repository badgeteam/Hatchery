<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class VoteFactory.
 *
 * @extends Factory<Vote>
 * @author annejan@badge.team
 */
class VoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Vote>
     */
    protected $model = Vote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, ProjectFactory>
     */
    public function definition()
    {
        return [
            'project_id' => Project::factory(),
        ];
    }
}
