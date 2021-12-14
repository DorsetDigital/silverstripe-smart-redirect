<?php

namespace DorsetDigital\SmartRedirect\Page;

use DorsetDigital\SmartRedirect\Control\SmartRedirectController;
use DorsetDigital\SmartRedirect\Model\QRRedirect;
use DorsetDigital\SmartRedirect\Model\RedirectRule;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\TempFolder;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
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
    private static $db = [
        'QRColourR' => 'Int',
        'QRColourG' => 'Int',
        'QRColourB' => 'Int',
        'LogoWidth' => 'Float'
    ];
    private static $has_one = [
        'QRLogo' => Image::class
    ];
    private static $has_many = [
        'Rules' => RedirectRule::class
    ];
    private static $owns = [
        'QRLogo'
    ];
    private static $defaults = [
        'QRColourR' => 0,
        'QRColourG' => 0,
        'QRColourB' => 0,
        'LogoWidth' => 25
    ];

    /**
     * @var int $qr_code_size
     * @config
     * Sets the size of the generated QR code image
     */
    private static $qr_code_size = 1500;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Rules', 'Content']);
        $fields->addFieldsToTab('Root.Main', [
            FieldGroup::create(
                NumericField::create('QRColourR')->setTitle('Red'),
                NumericField::create('QRColourG')->setTitle('Green'),
                NumericField::create('QRColourB')->setTitle('Blue')
            )->setTitle('QR Code Colour')
                ->setDescription('Colour for the QR code, specify as RGB (0-255)'),
            UploadField::create('QRLogo')->setFolderName('QR')
                ->setDescription('Logo for inclusion in the QR code'),
            NumericField::create('LogoWidth')
                ->setDescription('Width of the logo as a percentage of the code. Please note:  setting this value too high may prevent the QR code from scanning')
        ]);
        $fields->insertAfter('URLSegment', LiteralField::create('note', '<p><em>Keep URLs as short as possible to reduce QR code complexity</em></p>'));
        if ($this->isInDB()) {

            $codeSize = $this->config()->get('qr_code_size');

            //Add the QR code to the CMS
            $qrURL = $this->AbsoluteLink();
            $qr = new QrCode($qrURL);
            $qr->setWriterByName('png');
            $qr->setEncoding('UTF-8');
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());
            $qr->setSize($codeSize);
            $qr->setForegroundColor(['r' => $this->QRColourR, 'g' => $this->QRColourG, 'b' => $this->QRColourB, 'a' => 0]);
            $qr->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);

            if ($this->QRLogoID > 0) {
                $logoContent = $this->QRLogo()->getString();
                $tempFile = TempFolder::getTempFolder(BASE_PATH) . DIRECTORY_SEPARATOR . $this->QRLogo()->getTitle();
                file_put_contents($tempFile, $logoContent);

                $qr->setLogoPath($tempFile);
                $qr->setLogoWidth($codeSize * ($this->LogoWidth / 100));
            }

            $qrImage = LiteralField::create('qrtemp',
                '<div style="text-align: center; max-width: 300px; width: 100%;">
<p><img src="' . $qr->writeDataUri() . '" style="max-width: 100%;" alt="QR Code preview">
</p><p>' . $qrURL . '</p>
<p><em>(Right-click the above image to download a larger version)</em></p>
</div><hr>');

            $grid = GridField::create('Rules', 'Rules', $this->Rules(), GridFieldConfig_RecordEditor::create()
                ->addComponent(new GridFieldSortableRows('SortOrder')));
            $fields->addFieldsToTab('Root.RedirectRules', [
                $qrImage,
                LiteralField::create('intro', '<p><strong>Rules are processed in the order shown below.  The first rule which matches will be used.  You should always include a default rule at the end, else the code will return a 404 error if no rules match.</strong></p><p></br/></p>'),
                $grid
            ]);
        } else {
            $fields->addFieldsToTab('Root.RedirectRules', [
                LiteralField::create('newrecord', '<p><strong>Click \'Create\' to save the redirect and begin adding rules</strong></p>')
            ]);
        }
        return $fields;
    }
}
