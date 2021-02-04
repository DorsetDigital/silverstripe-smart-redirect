<?php

namespace DorsetDigital\SmartRedirect\Model;

use DorsetDigital\SmartRedirect\Page\SmartRedirectPage;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;

class RedirectRule extends DataObject
{
    private static $table_name = 'QRRedirectRule';
    private static $db = [
        'RuleType' => 'Enum("Default,Location,Language")',
        'RuleConfig' => 'Text',
        'RedirectTo' => 'Varchar(255)',
        'SortOrder' => 'Int'
    ];
    private static $has_one = [
        'Redirect' => SmartRedirectPage::class
    ];
    private static $default_sort = 'SortOrder';
    private static $summary_fields = [
        'RuleType' => 'Rule Type',
        'RuleConfigDescription' => 'Config',
        'RedirectTo' => 'Redirect to'
    ];
    private static $singular_name = 'Redirect Rule';
    private static $plural_name = 'Redirect Rules';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fieldTypes = $this->dbObject('RuleType')->enumValues();
        $savedRule = $this->isInDB();

        foreach ($fieldTypes as $fieldType) {
            $fact = new RedirectFactory();
            if (($savedRule) && ($this->RuleType == $fieldType)) {
                $rule = $fact->getRule($this);
                $formFields = $rule->getFormFields();
                if ((is_array($formFields)) && (count($formFields) > 0)) {
                    array_unshift($formFields, LiteralField::create($fieldType . '-fields-open', '<div id="' . $fieldType . '-fields">'));
                    $formFields[] = LiteralField::create($fieldType . '-fields-close', '</div>');
                    $fields->addFieldsToTab('Root.Main', $formFields);
                }
            }
            if (!$savedRule) {
                $fields->addFieldsToTab('Root.Main', [
                    LiteralField::create('intro', '<p><strong>Select the rule type and save the record to start configuring.</strong></p>')
                ]);
            }


        }

        $fields->removeByName(['SortOrder', 'RedirectID']);
        $fields->dataFieldByName('RuleConfig')->setRows(2)->setReadOnly(true);
        $fields->dataFieldByName('RedirectTo')->setDescription('The full URL of the redirect, eg. https://www.example.com');

        return $fields;
    }

    public function getRuleConfigDescription()
    {
        if ($this->isInDB()) {
            $fact = new RedirectFactory();
            $rule = $fact->getRule($this);
            return $rule->getSummaryDescription();
        }
        return null;
    }


    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $fact = new RedirectFactory();
        $rule = $fact->getRule(null, $this->RuleType);
        $request = Controller::curr()->getRequest();
        $this->RuleConfig = $rule->buildConfigData($this, $request);
    }


}
