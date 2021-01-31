<?php

namespace DorsetDigital\SmartRedirect\Helper;

class FilesHelper
{

    /**
     * Recursively deletes the files in a given directory.
     * With great power, comes great responsibility!
     * @param $target
     */
    public static function delete_files($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK);

            foreach ($files as $file) {
                self::delete_files($file);
            }

            if (is_dir($target)) {
                rmdir($target);
            }
        } elseif (is_file($target)) {
            unlink($target);
        }
    }

}
