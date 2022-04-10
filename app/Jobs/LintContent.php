<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\ProjectUpdated;
use App\Models\File;
use App\Support\Linters;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class LintContent.
 *
 * @author annejan@badge.team
 */
class LintContent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var File */
    private $file;
    /** @var string|null */
    private $content;

    /**
     * Create a new job instance.
     *
     * @param File        $file
     * @param string|null $content
     *
     * @return void
     */
    public function __construct(File $file, string $content = null)
    {
        $this->file = $file;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->file->lintable) {
            event(new ProjectUpdated(
                $this->file->version->project,
                'File ' . $this->file->name . ' currently not lintable.',
                'info'
            ));

            return;
        }
        $data = Linters::lintFile($this->file, $this->content);

        if ($data['return_value'] === 0) {
            event(new ProjectUpdated(
                $this->file->version->project,
                'File ' . $this->file->name . ' linted successfully.'
            ));

            return;
        }

        if (!empty($data[0])) {
            event(new ProjectUpdated($this->file->version->project, (string) $data[0], 'warning'));

            return;
        }

        event(new ProjectUpdated($this->file->version->project, (string) $data[1], 'danger'));
    }
}
