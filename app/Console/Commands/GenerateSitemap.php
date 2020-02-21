<?php

namespace App\Console\Commands;

use App\Models\Project;
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
     * @return mixed
     */
    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setPriority(1)
                ->setLastModificationDate(Project::get()->last()->updated_at)
            )
            ->add(Url::create('/projects')
                ->setLastModificationDate(Project::get()->last()->updated_at)
            );

        Project::all()->each(function (Project $project) use ($sitemap) {
            $sitemap->add(Url::create("/projects/{$project->slug}")
                ->setLastModificationDate($project->updated_at)
                ->setPriority(0.5)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
