<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Badge;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(
                Url::create('/')
                ->setPriority(1)
                ->setLastModificationDate($this->getLastUpdated())
            )
            ->add(
                Url::create('/projects')
                    ->setLastModificationDate($this->getLastUpdated())
            )
            ->add(
                Url::create('/badges')
                    ->setLastModificationDate($this->getLastUpdatedBadge())
            )
            ->add(
                Url::create('/api')
                    ->setLastModificationDate($this->getLastUpdatedBadge())
            );

        Project::all()->each(function (Project $project) use ($sitemap) {
            $sitemap->add(
                Url::create(route('projects.show', $project->slug))
                ->setLastModificationDate($this->getLastUpdated($project))
                ->setPriority(0.5)
            );
        });
        Badge::all()->each(function (Badge $badge) use ($sitemap) {
            $sitemap->add(
                Url::create(route('badges.show', $badge->slug))
                    ->setLastModificationDate($this->getLastUpdatedBadge($badge))
                    ->setPriority(0.6)
            );
        });
        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    /**
     * @param Project|null $project
     *
     * @return Carbon
     */
    private function getLastUpdated($project = null): Carbon
    {
        if ($project === null) {
            /** @var Project|null $project */
            $project = Project::latest()->first();
        }

        return ($project === null || $project->updated_at === null) ? Carbon::now() : $project->updated_at;
    }

    /**
     * @param Badge|null $badge
     *
     * @return Carbon
     */
    private function getLastUpdatedBadge($badge = null): Carbon
    {
        if ($badge === null) {
            /** @var Badge|null $badge */
            $badge = Badge::latest()->first();
        }

        return ($badge === null || $badge->updated_at === null) ? Carbon::now() : $badge->updated_at;
    }
}
