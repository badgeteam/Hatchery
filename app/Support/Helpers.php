<?php

namespace App\Support;

use App\Models\File;
use App\Models\Version;
use ErrorException;

/**
 * Class Helpers.
 *
 * @author annejan@badge.team
 */
class Helpers
{
    /**
     * @param string $dir
     *
     * @return bool
     */
    public static function delTree(string $dir): bool
    {
        try {
            $files = scandir($dir);
            $files = $files ? array_diff($files, ['.', '..']) : [];
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
            }

            return rmdir($dir);
        } catch (ErrorException $e) {
            report($e);

            return false;
        }
    }

    /**
     * @param string  $dir
     * @param Version $version
     * @param string  $prefix
     *
     * @return void
     */
    public static function addFiles(string $dir, Version $version, $prefix = ''): void
    {
        $objects = scandir($dir);
        $objects = $objects ? array_diff($objects, ['.git', '.', '..']) : [];
        foreach ($objects as $object) {
            if (is_dir("$dir/$object")) {
                self::addFiles("$dir/$object", $version, "$prefix$object/");
            } else {
                if (File::valid($object)) {
                    $file = new File();
                    $file->user_id = $version->user_id;
                    $file->name = "$prefix$object";
                    $file->content = (string) file_get_contents("$dir/$object");
                    $file->version()->associate($version);
                    $file->save();
                }
            }
        }
    }

    /**
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[(int)$pow];
    }
}
