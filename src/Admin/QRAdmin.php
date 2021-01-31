<?php

namespace DorsetDigital\SmartRedirect\Admin;

use DorsetDigital\SmartRedirect\Model\QRRedirect;
use SilverStripe\Admin\ModelAdmin;

class QRAdmin extends ModelAdmin
{
    private static $managed_models = [
        QRRedirect::class
    ];
    private static $url_segment = 'qr-admin';
    private static $menu_title = 'QR Code admin';
    private static $menu_icon_class = 'font-icon-mobile';
}
