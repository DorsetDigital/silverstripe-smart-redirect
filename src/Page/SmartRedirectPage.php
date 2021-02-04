<?php

namespace DorsetDigital\SmartRedirect\Page;

use DorsetDigital\SmartRedirect\Control\SmartRedirectController;
use DorsetDigital\SmartRedirect\Model\QRRedirect;
use DorsetDigital\SmartRedirect\Model\RedirectRule;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class SmartRedirectPage extends \Page
{
    private static $allowed_children = 'none';
    private static $show_stage_link = false;
    private static $show_live_link = false;
    private static $icon_class = 'font-icon-p-redirect';
    private static $description = 'Smart redirect page';
    private static $controller_name = SmartRedirectController::class;
    private static $table_name = 'SmartRedirectPage';
    private static $singular_name = 'Smart redirect page';
    private static $plural_name = 'Smart redirect pages';
    private static $has_many = [
        'Rules' => RedirectRule::class
    ];


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Rules', 'Content']);
        if ($this->isInDB()) {

            //Add the QR for reference
            $qrURL = $this->AbsoluteLink();
            $qr = new QrCode($qrURL);
            $qr->setWriterByName('png');
            $qr->setEncoding('UTF-8');
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());
            $qr->setSize(250);
            $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
            $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);

            $qrImage = LiteralField::create('qrtemp', '<div style="text-align: center"><p><img src="' . $qr->writeDataUri() . '" alt="QR Code preview"></p><p>' . $qrURL . '</p></div><hr>');

            $grid = GridField::create('Rules', 'Rules', $this->Rules(), GridFieldConfig_RecordEditor::create()->addComponent(new GridFieldSortableRows('SortOrder')));
            $fields->addFieldsToTab('Root.RedirectRules', [
                $qrImage,
                LiteralField::create('intro', '<p><strong>Rules are processed in the order shown below.  The first rule which matches will be used.  You should always include a default rule at the end, else the code will return a 404 error if no rules match.</strong></p><p></br/></p>'),
                $grid
            ]);
        } else {
            $fields->addFieldToTab('Root.RedirectRules', LiteralField::create('newrecord', '<p><strong>Click \'Create\' to save the redirect and begin adding rules</strong></p>'));
        }
        return $fields;
    }
}
