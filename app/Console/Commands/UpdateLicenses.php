<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateLicenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update licenses from SPDX';

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
     * @return int
     */
    public function handle()
    {
        $contents = Http::get('https://github.com/spdx/license-list-data/raw/master/json/licenses.json')->body();
        file_put_contents(resource_path('assets/licenses.json'), $contents);
        return 0;
    }
}
