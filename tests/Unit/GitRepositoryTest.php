<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Helpers;
use CzProject\GitPhp\Git;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class GitRepositoryTest.
 *
 * @author annejan@badge.team
 */
class GitRepositoryTest extends TestCase
{
    use WithFaker;

    /**
     * Assert we can open a repository.
     */
    public function testGitRepositoryOpening(): void
    {
        $path = sys_get_temp_dir() . '/' . Str::slug($this->faker->name);
        mkdir($path);
        $repo = new Git();
        $repo = $repo->open($path);
        if (PHP_OS === 'Darwin') {
            $this->assertEquals('/private' . $path, $repo->getRepositoryPath());
        } else {
            $this->assertEquals($path, $repo->getRepositoryPath());
        }
        Helpers::delTree($path);
    }

    /**
     * Assert we can open a repository .git.
     */
    public function testGitRepositoryOpeningGit(): void
    {
        $path = sys_get_temp_dir() . '/' . Str::slug($this->faker->name);
        mkdir($path);
        $gitPath = $path . '/.git';
        mkdir($gitPath);

        $repo = new Git();
        $repo = $repo->open($gitPath);
        if (PHP_OS === 'Darwin') {
            $this->assertEquals('/private' . $path, $repo->getRepositoryPath());
        } else {
            $this->assertEquals($path, $repo->getRepositoryPath());
        }
        Helpers::delTree($path);
    }
}
