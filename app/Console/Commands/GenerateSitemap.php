<?php

namespace App\Console\Commands;

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
            );

        Project::all()->each(function (Project $project) use ($sitemap) {
            $sitemap->add(
                Url::create(route('projects.show', $project->slug))
                ->setLastModificationDate($project->updated_at)
                ->setPriority(0.5)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    private function getLastUpdated(): Carbon
    {
        /** @var Project|null $project */
        $project = Project::get()->last();
        return $project === null ? Carbon::now() : $project->updated_at;
    }
}
