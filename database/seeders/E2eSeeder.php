<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Fixtures for the browser tests in tests/e2e.
 *
 * Not part of DatabaseSeeder: this is only ever run explicitly, with
 * `php artisan db:seed --class=E2eSeeder`.
 *
 * @author annejan@badge.team
 */
class E2eSeeder extends Seeder
{
    public const EMAIL = 'e2e@badge.team';
    public const PASSWORD = 'e2e-password';
    public const PROJECT = 'E2E Editor';
    public const FILE = 'README.md';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var User $user */
        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name'     => 'End To End',
                'password' => Hash::make(self::PASSWORD),
                'editor'   => 'default',
                'public'   => true,
            ]
        );

        // Projects associate themselves with the authenticated user on create.
        Auth::login($user);

        /** @var Project $project */
        $project = Project::firstOrCreate(
            ['name' => self::PROJECT],
            ['category_id' => 1, 'user_id' => $user->id]
        );

        // Make sure the fixture is owned by the fixture user even if a project
        // with this name was already lying around.
        if ($project->user_id !== $user->id) {
            $project->user_id = $user->id;
            $project->save();
        }

        /** @var Version $version */
        $version = $project->versions()->firstOrFail();

        $version->files()->firstOrCreate(
            ['name' => self::FILE],
            ['content' => "# Hello\n\nsome *markdown* text\n"]
        );

        Auth::logout();
    }
}
