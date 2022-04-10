<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\File;

/**
 * Class Linters.
 *
 * @author annejan@badge.team
 */
class Linters
{
    /**
     * @param File        $file
     * @param string|null $content
     *
     * @return array<string|int, string|int|null>
     */
    public static function lintFile(File $file, $content = null)
    {
        $command = 'pyflakes';
        if ($file->extension === 'md') {
            $command = base_path('node_modules/.bin/markdownlint  -s');
        } elseif ($file->extension === 'v') {
            $command = base_path('linters/ice40');
        } elseif ($file->extension === 'json') {
            $command = base_path('vendor/bin/jsonlint');
        }

        if ($content === null) {
            $content = $file->content;
        }

        return self::lintContent($content, $command);
    }

    /**
     * @param string $content
     * @param string $command = "pyflakes"
     *
     * @return array<string|int, string|int|null>
     */
    public static function lintContent(string $content, string $command = 'pyflakes'): array
    {
        $stdOut = $stdErr = '';
        $returnValue = 255;
        $fds = [
            0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
        ];
        $process = proc_open($command, $fds, $pipes, null, null);
        if (is_resource($process)) {
            fwrite($pipes[0], $content . "\n");   // insert trailing newline ;)
            fclose($pipes[0]);
            $stdOut = (string) stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stdErr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $returnValue = proc_close($process);
        }

        return [
            'return_value' => $returnValue,
            0              => preg_replace('/[<|(]?stdin[)|>]?\:/', '', $stdOut),
            1              => preg_replace('/[<|(]?stdin[)|>]?\:/', '', $stdErr),
        ];
    }
}
