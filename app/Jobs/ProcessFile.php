<?php

namespace App\Jobs;

use App\Events\ProjectUpdated;
use App\Models\Badge;
use App\Models\File;
use App\Support\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /** @var File */
    private $file;
    /** @var string */
    private $tempFolder;

    /**
     * Create a new job instance.
     *
     * @param File $file
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        $this->tempFolder = sys_get_temp_dir().'/vhdl/'.$this->file->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->file->processable) {
            event(new ProjectUpdated($this->file->version->project, 'File '.$this->file->name.' currently not processable.', 'info'));

            return;
        }

        try {
            $this->process($this->file->extension);
            event(new ProjectUpdated($this->file->version->project, 'File '.$this->file->name.' processed successfully.'));

            return;
        } catch (\Throwable $exception) {
            event(new ProjectUpdated($this->file->version->project, $exception->getMessage(), 'danger'));
        }
    }

    /**
     * @param string $extension
     *
     * @throws \Throwable
     */
    private function process(string $extension): void
    {
        if ($extension === 'v') {
            $badges = $this->file->version->project->badges()->whereNotNull('constraints')->get();
            if ($badges->count() === 0) {
                throw new \Exception('No badges with workable constraints for project: '.$this->file->version->project->name);
            }

            $this->ensureWorkDirExists();

            file_put_contents($this->tempFolder.'/'.$this->file->name, $this->file->content, LOCK_EX);

            /** @var Badge $badge */
            foreach ($badges as $badge) {
                file_put_contents($this->tempFolder.'/'.$badge->slug.'.pcf', $badge->constraints, LOCK_EX);
                $this->synthesize($badge);
            }

            Helpers::delTree($this->tempFolder);

            return;
        }

        // @codeCoverageIgnoreStart
        throw new \Exception("Don't know how to process <strong>$extension<strong> file.");
        // @codeCoverageIgnoreEnd
    }

    /**
     * A cleaner way to create a working directory.
     */
    private function ensureWorkDirExists(): void
    {
        $base = sys_get_temp_dir().'/';
        $path = str_replace($base, '', $this->tempFolder);
        $dirs = explode('/', $path);
        foreach ($dirs as $dir) {
            $base .= $dir;
            if (!is_dir($base)) {
                mkdir($base);
            }
            $base .= '/';
        }
    }

    /**
     * @param Badge $badge
     */
    private function synthesize(Badge $badge): void
    {
        // @todo synth for now . . dummy

        // yosys -q -p "read_verilog -noautowire $^ ; check ; clean ; synth_ice40 -blif $@"

        $this->file->version->files()->create(['name' => $this->file->baseName.'_'.$badge->slug.'.bin']);
    }
}
