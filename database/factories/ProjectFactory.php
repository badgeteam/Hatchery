<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ProjectFactory.
 *
 * @author annejan@badge.team
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, CategoryFactory|string>
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->name,
            'category_id' => Category::factory(),
        ];
    }
}
