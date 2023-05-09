<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class CategoryFactory.
 *
 * @extends Factory<Category>
 * 
 * @author annejan@badge.team
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Category>
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return string[]
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
