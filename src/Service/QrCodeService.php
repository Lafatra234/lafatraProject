<?php
namespace App\Service;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    private $writer;

    public function __construct() 
    {
        $this->writer = new PngWriter();
    }

    public function makeQr($contenu)
    {
        $qrCode = QrCode::create($contenu)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(120)
            ->setMargin(40)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $label = Label::create('')->setFont(new NotoSans(8));

        $qrCodes = [];
        $qrCodes['simple'] = $this->writer->write(
            $qrCode,
            null,
            $label->setText('Votre Billet d\'entré')
        )->getDataUri();

        return $qrCodes;
    }
}