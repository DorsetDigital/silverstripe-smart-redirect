<?php

namespace DorsetDigital\SmartRedirect\Model;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class QRRedirect extends DataObject
{
    private static $table_name = 'QRRedirect';
    private static $db = [
        'Title' => 'Varchar',
        'URLSegment' => 'Varchar'
    ];
    private static $has_many = [
        'Rules' => QRRedirectRule::class
    ];
    private static $indexes = [
        'URLSegment' => true
    ];
    private static $singular_name = 'QR Redirect';
    private static $plural_name = 'QR Redirects';
    private static $summary_fields = [
        'Title' => 'Code',
        'URLSegment' => 'URL'
    ];

    public function validate()
    {
        $result = parent::validate();
        if ((!$this->isInDB()) || ($this->isInDB() && $this->isChanged())) {
            $test = $this->getByURLSegment($this->URLSegment);
            if (($test) && ($test->ID > 0)) {
                $result->addError('This URL segment already exists.  Please try a different one.');
            }
        }
        return $result;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->URLSegment = trim($this->URLSegment, '/ \t');
    }

    public static function getByURLSegment($segment)
    {
        return self::get_one(self::class, ['URLSegment' => $segment]);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Rules']);
        if ($this->isInDB()) {

            //Add the QR for reference
            $qrURL = Controller::join_links([
                Director::absoluteBaseURL(),
                'qr',
                $this->URLSegment
            ]);
            $qr = new QrCode($qrURL);
            $qr->setWriterByName('png');
            $qr->setEncoding('UTF-8');
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());
            $qr->setSize(250);
            $qr->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
            $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);

            $qrImage = LiteralField::create('qrtemp', '<div style="text-align: center"><p><img src="' . $qr->writeDataUri() . '" alt="QR Code preview"></p><p>' . $qrURL . '</p></div><hr>');

            $grid = GridField::create('Rules', 'Rules', $this->Rules(), GridFieldConfig_RecordEditor::create()->addComponent(new GridFieldSortableRows('SortOrder')));
            $fields->addFieldsToTab('Root.Main', [
                $qrImage,
                LiteralField::create('intro', '<p><strong>Rules are processed in the order shown below.  The first rule which matches will be used.  You should always include a default rule at the end, else the code will return a 404 error if no rules match.</strong></p><p></br/></p>'),
                $grid
            ]);
        } else {
            $fields->addFieldToTab('Root.Main', LiteralField::create('newrecord', '<p><strong>Click \'Create\' to save the redirect and begin adding rules</strong></p>'));
        }
        $fields->dataFieldByName('Title')->setDescription('For reference only, to identify the code / campaign');
        $fields->dataFieldByName('URLSegment')->setDescription('The end part of the URL.  Do not include any leading or trailing slashes!');
        return $fields;
    }
}
