<?php

namespace DorsetDigital\SmartRedirect\Model;

use DorsetDigital\SmartRedirect\Model\Rule\DefaultRule;
use DorsetDigital\SmartRedirect\Model\Rule\LanguageRule;
use DorsetDigital\SmartRedirect\Model\Rule\LocationRule;
use DorsetDigital\SmartRedirect\Model\Rule\TimedRuled;

class RedirectFactory
{

    /**
     * @param RedirectRule|null $rule
     * @param null $ruleType
     * @return DefaultRule|false
     */
    public function getRule(RedirectRule $rule = null, $ruleType = null)
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
            case 'timed':
                return new TimedRuled($rule);
            default:
                return false;
        }
    }
}
