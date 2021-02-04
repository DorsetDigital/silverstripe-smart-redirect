<?php

namespace DorsetDigital\SmartRedirect\Model\Rule;

use DorsetDigital\SmartRedirect\Helper\MaxMindHelper;
use DorsetDigital\SmartRedirect\Model\RedirectBase;
use League\ISO3166\ISO3166;
use MaxMind\Db\Reader;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\ListboxField;

class LocationRule extends RedirectBase
{
    public function checkRule()
    {
        $config = json_decode($this->getConfig(), true);

        if (($this->isValidRedirect() === false) || (!$config) || (!isset($config['type']))) {
            return false;
        }

        switch ($config['type']) {
            case 'country':
                return $this->checkCountryMatch($config['match']);

            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    private function checkCountryMatch($matchConfig)
    {
        $mmDB = MaxMindHelper::getLocalDBFilePath();
        if (!is_file($mmDB)) {
            return false;
        }
        $reader = new Reader($mmDB);
        $record = $reader->get($this->getUserIP());

        if ($record) {

            $userCode = false;

            if (isset($record['country'])) {
                $userCode = strtolower($record['country']['iso_code']);
            } else if (isset($record['registered_country'])) {
                $userCode = strtolower($record['registered_country']['iso_code']);
            }

            if ($userCode !== false) {
                $matches = explode(',', $matchConfig);

                if (in_array($userCode, array_map('strtolower', $matches))) {
                    return true;
                }
            }
        }
        return false;
    }


    private function getUserIP()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
                $addr = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($addr[0]);
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function getFormFields()
    {
        $configJSON = $this->getConfig();
        $config = json_decode($configJSON, true);
        $codeValue = null;
        if (($config) && (isset($config['match']))) {
            $codeValue = explode(",", $config['match']);
        }
        $fields = parent::getFormFields();
        $fields[] = ListboxField::create('CountryCodes', 'Target Countries')->setSource($this->getCountrySelectOpts())->setValue($codeValue);
        return $fields;
    }

    private function getCountrySelectOpts()
    {
        $league = new ISO3166();
        $allCountries = $league->all();
        $opts = [];
        foreach ($allCountries as $country) {
            $code = strtolower($country['alpha2']);
            $name = $country['name'];
            $opts[$code] = $name;
        }
        return $opts;
    }

    public function buildConfigData($obj, HTTPRequest $request = null)
    {
        $codes = [];
        if ($request) {
            $codes = $request->postVar('CountryCodes') ?: [];
        }
        $config = [
            'type' => 'country',
            'match' => implode(",", $codes)
        ];
        return json_encode($config);
    }


    public function getSummaryDescription()
    {
        $config = json_decode($this->getConfig(), true);
        $countryList = [];
        if (($config) && (isset($config['match']))) {
            $countryCodes = explode(",", $config['match']);
            foreach ($countryCodes as $countryCode) {
                $countryRecord = (new ISO3166())->alpha2($countryCode);
                if ($countryRecord) {
                    $countryList[] = $countryRecord['name'];
                }
            }
        }

        return implode(", ", $countryList);
    }
}
