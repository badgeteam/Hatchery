<?php

declare(strict_types=1);

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

/**
 * Class ProcessFile.
 *
 * @author annejan@badge.team
 */
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
    /** @var array<string> */
    private $files = [];

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
        $this->tempFolder = sys_get_temp_dir() . '/vhdl/' . $this->file->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->file->processable) {
            event(new ProjectUpdated(
                $this->file->version->project,
                'File ' . $this->file->name . ' currently not processable.',
                'info'
            ));

            return;
        }

        try {
            $this->process($this->file->extension);
            foreach ($this->files as $file) {
                event(new ProjectUpdated($this->file->version->project, 'File ' . $file . ' generated.'));
            }

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
            $badges = $this->file->version->project->badges()->whereNotNull('commands')->get();
            if ($badges->count() === 0) {
                throw new \Exception('No badges with workable commands for project: ' .
                    $this->file->version->project->name);
            }

            $this->ensureWorkDirExists();

            file_put_contents($this->tempFolder . $this->file->name, $this->file->content, LOCK_EX);

            /** @var Badge $badge */
            foreach ($badges as $badge) {
                if ($badge->constraints) {
                    file_put_contents($this->tempFolder . $badge->slug . '.pcf', $badge->constraints, LOCK_EX);
                    $this->synthesize($badge);
                } else {
                    event(new ProjectUpdated($this->file->version->project, 'No constraints for badge: ' .
                        $badge->name, 'warning'));
                }
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
        $base = sys_get_temp_dir() . '/';
        $path = str_replace($base, '', $this->tempFolder);
        $dirs = explode('/', $path);
        foreach ($dirs as $dir) {
            $base .= $dir;
            if (!is_dir($base)) {
                if (!mkdir($base) && !is_dir($base)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $base));
                }
            }
            $base .= '/';
        }
        $this->tempFolder .= '/';
    }

    /**
     * @param Badge $badge
     */
    private function synthesize(Badge $badge): void
    {
        $name = $this->file->baseName . '_' . $badge->slug . '.bin';

        $vdlFile = $this->tempFolder . $this->file->name;
        $pcfFile = $this->tempFolder . $badge->slug . '.pcf';
        $outFile = $this->tempFolder . $name;

        foreach (explode("\n", (string) $badge->commands) as $command) {
            $command = str_replace(['VDL', 'PCF', 'OUT'], [$vdlFile, $pcfFile, $outFile], $command);
            if ($this->execute($command) > 0) {
                return;
            }
        }

        $this->file->version->files()->firstOrCreate(['name' => $name], ['content' => file_get_contents($outFile)]);
        $this->files[] = $name;
    }

    /**
     * @param string $command
     *
     * @return int
     */
    private function execute(string $command): int
    {
        $stdOut = $stdErr = '';
        $returnValue = 255;
        $fds = [
            0 => ['pipe', 'r'], // stdin is a pipe that the child can read from
            1 => ['pipe', 'w'], // stdout is a pipe that the child might write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child might write to
        ];
        $process = proc_open($command, $fds, $pipes, null, null);
        if (is_resource($process)) {
            $stdOut = (string) stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stdErr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $returnValue = proc_close($process);
        }

        if ($returnValue > 0) {
            if ($stdErr) {
                event(new ProjectUpdated(
                    $this->file->version->project,
                    str_replace($this->tempFolder, '', $stdErr),
                    'danger'
                ));
            }
            if ($stdOut) {
                event(new ProjectUpdated(
                    $this->file->version->project,
                    str_replace($this->tempFolder, '', $stdOut),
                    'warning'
                ));
            }
        }

        return $returnValue;
    }
}
