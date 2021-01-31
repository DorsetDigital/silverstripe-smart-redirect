<?php

namespace DorsetDigital\SmartRedirect\Helper;

class BrowserLanguageHelper
{

    private $allCountries = [];

    public function __construct()
    {
        $dataFile = __DIR__ . '/Langs/supported_langs.csv';
        $handle = fopen($dataFile, 'r');
        if (!$handle) {
            die('No language file found');
        }
        $list = [];
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $code = $data[0];
            $name = $data[1];

            if (($code != "") && ($name != "")) {
                $list[$code] = $name;
            }
        }
        $this->setCountryList($list);
    }

    public function getCountryList()
    {
        return $this->allCountries;
    }

    private function setCountryList(array $list)
    {
        $this->allCountries = $list;
    }

    public function getCountryNameByCode($code)
    {
        $allCountries = $this->getCountryList();
        if (isset($allCountries[$code])) {
            return $allCountries[$code];
        }
        return null;
    }

}
