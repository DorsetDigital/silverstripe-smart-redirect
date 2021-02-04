<?php

namespace DorsetDigital\SmartRedirect\Model\Rule;

use DorsetDigital\SmartRedirect\Model\RedirectBase;

class DefaultRule extends RedirectBase
{
    public function checkRule()
    {
        if ($this->isValidRedirect()) {
            return true;
        }
        return false;
    }
}
