<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class VersionFactory.
 *
 * @extends Factory<Version>
 * @author annejan@badge.team
 */
class VersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Version>
     */
    protected $model = Version::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, ProjectFactory|int>
     */
    public function definition()
    {
        return [
            'revision'   => 1,
            'project_id' => Project::factory(),
        ];
    }
}
