<?php

namespace DorsetDigital\SmartRedirect\Model\Rule;

use DorsetDigital\SmartRedirect\Helper\BrowserLanguageHelper;
use DorsetDigital\SmartRedirect\Model\QRBase;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\FieldType\DBField;
use Teto\HTTP\AcceptLanguage;

class LanguageRule extends QRBase
{
    public function checkRule()
    {
        $config = json_decode($this->getConfig(), true);

        if (($this->isValidRedirect() === false) || (!$config) || (!isset($config['type'])) || (!isset($config['lang']))) {
            return false;
        }

        switch ($config['type']) {
            case 'match':
                return $this->isLanguageMatch($config['lang']);
            case 'exclude':
                return !$this->isLanguageMatch($config['lang']);
            default:
                return false;
        }
    }

    /**
     * @param string $lang
     * @return boolean
     */
    private function isLanguageMatch($lang)
    {
        $matchLangs = explode(',', $lang);
        $browserLangs = AcceptLanguage::get();

        if ((is_array($matchLangs)) && ($browserLangs)) {
            $acceptedLangs = [];
            foreach ($browserLangs as $browserLang) {
                $acceptedLang = strtolower($browserLang['language']);
                if ((isset($browserLang['region'])) && ($browserLang['region'] != '')) {
                    $acceptedLang .= '_' . strtolower($browserLang['region']);
                }
                $acceptedLangs[] = $acceptedLang;
            }
        }

        if (count(array_intersect($matchLangs, $acceptedLangs)) > 0) {
            return true;
        }

        return false;
    }

    public function getFormFields()
    {
        $fields = parent::getFormFields();
        $langHelper = new BrowserLanguageHelper();

        $configJSON = $this->getConfig();
        $config = json_decode($configJSON, true);
        $codeValue = null;
        if (($config) && (isset($config['lang']))) {
            $codeValue = explode(",", $config['lang']);
        }
        $defaultCompare = (isset($config['type'])) ? $config['type'] : 'match';

        $fields[] = DropdownField::create('MatchType', 'Comparison Type')->setSource(['match' => 'Match languages', 'exclude' => 'Exclude languages'])->setValue($defaultCompare);
        $fields[] = ListboxField::create('AcceptedLangs', 'Target Languages')->setSource($langHelper->getCountryList())->setValue($codeValue);
        return $fields;
    }


    public function buildConfigData($obj, HTTPRequest $request = null)
    {
        $codes = [];
        $comparison = 'match';
        if ($request) {
            $codes = $request->postVar('AcceptedLangs') ?: [];
            $comparison = $request->postVar('MatchType');
        }
        $config = [
            'type' => $comparison,
            'lang' => implode(",", $codes)
        ];
        return json_encode($config);
    }

    public function getSummaryDescription()
    {
        $config = json_decode($this->getConfig(), true);
        $langList = [];
        if (($config) && (isset($config['lang'])) && (isset($config['type']))) {
            $langHelper = new BrowserLanguageHelper();

            $langCodes = explode(",", $config['lang']);
            foreach ($langCodes as $langCode) {
                $langName = $langHelper->getCountryNameByCode($langCode);
                if ($langName) {
                    $langList[] = $langName;
                }
            }
        }

        return DBField::create_field('HTMLFragment', ucfirst($config['type']) . ": <em>" . implode(", ", $langList) . "</em>");
    }


}
