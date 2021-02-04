<?php

namespace DorsetDigital\SmartRedirect\Control;

use DorsetDigital\SmartRedirect\Model\RedirectFactory;
use DorsetDigital\SmartRedirect\Model\QRRedirect;
use SilverStripe\Control\HTTPRequest;

class SmartRedirectController extends \PageController
{
    private static $url_handlers = [
        '//$Segment!' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        $redirect = $this->data();

        foreach ($redirect->Rules() as $redirectRule) {
            $factory = new RedirectFactory();
            $rule = $factory->getRule($redirectRule);
            if (($rule) && ($rule->checkRule() === true)) {
                return $this->redirect($rule->getRedirect());
                //echo "<p>Matched ".$redirectRule->RuleType." rule - redirecting to ".$rule->getRedirect()."</p>";
            } else {
                //echo "<p>Skipping ".$redirectRule->RuleType." rule</p>";
            }
        }
        return $this->httpError(404);
    }
}
