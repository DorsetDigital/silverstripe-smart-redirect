<?php


namespace DorsetDigital\SmartRedirect\Model\Rule;


use DorsetDigital\SmartRedirect\Model\RedirectBase;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\ORM\FieldType\DBField;

class TimedRuled extends RedirectBase
{

    public function checkRule()
    {
        $config = json_decode($this->getConfig(), true);
        if (($this->isValidRedirect() === false) || (!$config) || (!isset($config['from'])) || (!isset($config['to']))) {
            return false;
        }
        return $this->inDateRange($config['from'], $config['to']);
    }

    private function inDateRange($from, $to)
    {
        $now = time();
        $start = strtotime($from);
        $end = strtotime($to);

        if (($now > $start) && ($now < $end)) {
            return true;
        }

        return false;
    }


    public function getFormFields()
    {
        $fields = parent::getFormFields();

        $configJSON = $this->getConfig();
        $config = json_decode($configJSON, true);

        $fromField = DatetimeField::create('FromTime', 'Start date/time')->setHTML5(true);
        $toField = DatetimeField::create('ToTime', 'End date/time')->setHTML5(true);

        if ((isset($config['from'])) && (!is_array($config['from']))) {
            $fromValue = (string)$config['from'] . ":00";
            $fromField->setValue($fromValue);
        }
        if ((isset($config['to'])) && (!is_array($config['to']))) {
            $toValue = (string)$config['to'] . ":00";
            $toField->setValue($toValue);
        }

        $fields[] = $fromField;
        $fields[] = $toField;

        return $fields;
    }


    public function buildConfigData($obj, HTTPRequest $request = null)
    {
        if ($request) {
            $from = $request->postVar('FromTime') ?: [];
            $to = $request->postVar('ToTime');
        }
        $config = [
            'from' => $from,
            'to' => $to
        ];
        return json_encode($config);
    }

    public function getSummaryDescription()
    {
        $format = 'jS M Y - H:i';
        $config = json_decode($this->getConfig(), true);
        $from = '';
        $to = '';
        if (($config) && (isset($config['from'])) && (isset($config['to']))) {
            $from = date($format, strtotime($config['from']));
            $to = date($format, strtotime($config['to']));
        }

        return DBField::create_field('HTMLFragment', "From: <em>" . $from . "</em> To: <em>" . $to . "</em>");
    }

}
