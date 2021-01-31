<?php

namespace DorsetDigital\SmartRedirect\Model\Rule;

use DorsetDigital\SmartRedirect\Model\QRBase;

class DefaultRule extends QRBase
{
    public function checkRule()
    {
        if ($this->isValidRedirect()) {
            return true;
        }
        return false;
    }
}
