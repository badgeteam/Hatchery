<?php

namespace App\Support;

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
        $files = scandir($dir);
        if (!$files) {
            return false;
        }
        $files = array_diff($files, ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}