<?php

namespace DorsetDigital\SmartRedirect\Model;

abstract class QRBase
{
    private $ruleConfig;
    private $redirectURL;

    public function __construct($record)
    {
        //Hydrate the class properties
        if ($record) {
            $this->redirectURL = $record->RedirectTo;
            $this->ruleConfig = $record->RuleConfig;
        }
    }

    public function checkRule()
    {
    }

    public function getRedirect()
    {
        return $this->redirectURL;
    }

    /**
     * Returns an array of FormField objects
     * @return array
     */
    public function getFormFields()
    {
        return [];
    }

    public function getConfig()
    {
        return $this->ruleConfig;
    }

    public function buildConfigData($obj)
    {
        return null;
    }

    protected function isValidRedirect()
    {
        $redirect = $this->getRedirect();
        if (($redirect != "") && (filter_var($redirect, FILTER_VALIDATE_URL) !== false)) {
            return true;
        }
        return false;
    }

    public function getSummaryDescription() {
        return null;
    }


}
