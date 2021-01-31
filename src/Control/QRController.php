<?php

namespace DorsetDigital\SmartRedirect\Control;

use DorsetDigital\SmartRedirect\Model\QRFactory;
use DorsetDigital\SmartRedirect\Model\QRRedirect;
use SilverStripe\Control\HTTPRequest;

class QRController extends \PageController
{
    private static $url_handlers = [
        '//$Segment!' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        $urlSegment = trim($request->param('Segment'));
        if ($urlSegment == "") {
            return $this->httpError(404);
        }
        $redirect = QRRedirect::getByURLSegment($urlSegment);
        if (!$redirect) {
            return $this->httpError(404);
        }

        foreach ($redirect->Rules() as $redirectRule) {
            $factory = new QRFactory();
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
