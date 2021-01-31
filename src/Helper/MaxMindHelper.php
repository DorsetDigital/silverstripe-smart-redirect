<?php

namespace DorsetDigital\SmartRedirect\Helper;

use GuzzleHttp\Client;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;

class MaxMindHelper
{
    use Configurable;

    /**
     * @var string
     * @config
     */
    private static $download_base = 'https://download.maxmind.com/app/geoip_download';

    /**
     * @var string
     * @config
     */
    private static $edition_id = 'GeoLite2-Country';

    /**
     * @var string
     * @config
     */
    private static $db_filename = 'GeoLite2-Country.mmdb';

    /**
     * @var string
     * @config
     */
    private static $download_file_suffix = 'tar.gz';

    /**
     * @var string
     * @config
     */
    private static $local_dir = 'GeoData';

    /**
     * @var string
     * @config
     */
    private static $temp_dir = 'temp';

    /**
     * @var string
     * @config
     */
    private static $db_file_tempname = 'countryDB';


    private static function getLicenceKey()
    {
        $key = Environment::getEnv('MAXMIND_LICENCE_KEY');
        return $key == '' ? false : $key;
    }

    public static function downloadDatabase()
    {

        $licenceKey = self::getLicenceKey();
        if ($licenceKey === false) {
            return false;
        }

        $query = [
            'edition_id' => self::config()->get('edition_id'),
            'license_key' => $licenceKey,
            'suffix' => self::config()->get('download_file_suffix')
        ];

        $baseURL = self::config()->get('download_base');
        $tempLocation = self::getLocalDBLocation() . '/' . self::config()->get('db_file_tempname') . '.' . self::config()->get('download_file_suffix');

        $client = new Client();
        $response = $client->request('GET', $baseURL, [
            'query' => $query,
            'sink' => $tempLocation
        ]);

        if ($response->getStatusCode() < 400) {
            echo "<p>DB bundle downloaded to " . $tempLocation . "</p>";
            return self::extractDatabaseFiles($tempLocation);
        }
        return false;
    }

    public static function checkStructure()
    {
        $tempLocation = self::getLocalTempLocation() . "/";
        echo "<p>Checking existence of " . $tempLocation . "</p>";
        if (!is_dir($tempLocation)) {
            echo "<p>Not found - Creating " . $tempLocation . "</p>";
            mkdir($tempLocation, 0755, true);
        }
    }

    private static function getLocalDBLocation()
    {
        $localDir = self::config()->get('local_dir');
        return __DIR__ . '/' . $localDir;
    }

    private static function getLocalTempLocation()
    {
        $tempDir = self::config()->get('temp_dir');
        return self::getLocalDBLocation() . "/" . $tempDir;
    }

    private static function extractDatabaseFiles($tempfile)
    {
        try {
            $phar = new \PharData($tempfile);
            $phar->extractTo(self::getLocalTempLocation(), null, true);
            echo "<p>Extracted archive</p>";
            unlink($tempfile);
            echo "<p>Removed temporary file</p>";
            return self::copyDBFile();
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private static function copyDBFile()
    {
        $tempDir = self::getLocalTempLocation() . '/';
        echo "<p>Looking for a database file (" . $dbFilename = self::config()->get('db_filename') . ") in " . $tempDir . "</p>";
        self::recursiveCopy($tempDir);
        FilesHelper::delete_files($tempDir);

        $dbFile = self::getLocalDBFilePath();
        if ((is_file($dbFile)) && (filemtime($dbFile) > strtotime("1 day ago"))) {
            return true;
        }

        return false;
    }


    private static function recursiveCopy($dir)
    {
        $dbFilename = self::config()->get('db_filename');
        $tree = glob(rtrim($dir, '/') . '/*');
        if (is_array($tree)) {
            foreach ($tree as $file) {
                if (is_dir($file)) {
                    self::recursiveCopy($file);
                } elseif (is_file($file)) {
                    if (basename($file) == $dbFilename) {
                        copy($file, self::getLocalDBLocation() . "/" . $dbFilename);
                        echo "<p>" . $file . " copied to " . self::getLocalDBLocation() . "</p>";
                    }

                }
            }
        }
    }

    public static function getLocalDBFilePath()
    {
        return self::getLocalDBLocation() . "/" . self::config()->get('db_filename');
    }
}
