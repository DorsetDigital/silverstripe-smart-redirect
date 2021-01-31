<?php

namespace DorsetDigital\SmartRedirect\Model;

use DorsetDigital\SmartRedirect\Model\Rule\DefaultRule;
use DorsetDigital\SmartRedirect\Model\Rule\LanguageRule;
use DorsetDigital\SmartRedirect\Model\Rule\LocationRule;

class QRFactory
{

    /**
     * @param QRRedirectRule|null $rule
     * @param null $ruleType
     * @return DefaultRule|false
     */
    public function getRule(QRRedirectRule $rule = null, $ruleType = null)
    {
        if ($ruleType == '') {
            $ruleType = $rule->RuleType;
        }

        switch (strtolower($ruleType)) {
            case 'default':
                return new DefaultRule($rule);
            case 'location':
                return new LocationRule($rule);
            case 'language':
                return new LanguageRule($rule);
            default:
                return false;
        }
    }
}
