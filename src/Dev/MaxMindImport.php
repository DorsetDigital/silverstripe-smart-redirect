<?php

namespace DorsetDigital\SmartRedirect\Dev;

use DorsetDigital\SmartRedirect\Helper\MaxMindHelper;
use SilverStripe\Dev\BuildTask;

class MaxMindImport extends BuildTask
{

    private static $segment = 'importgeodatabase';
    protected $title = 'Import Geo DB';
    protected $description = 'Download and extract the geolocation database from MaxMind';

    public function run($request)
    {
        MaxMindHelper::checkStructure();

        $res = MaxMindHelper::downloadDatabase();
        if ($res) {
            echo "\n<p>Download complete</p>";
        } else {
            echo "\n<p>Error getting database</p>";
        }
    }


}

