<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\File;
use App\Models\Version;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class FileFactory.
 *
 * @author annejan@badge.team
 */
class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, VersionFactory|string>
     */
    public function definition()
    {
        return [
            'version_id' => Version::factory(),
            'name'       => $this->faker->word . '.py',
            'content'    => $this->faker->paragraph,
        ];
    }
}
