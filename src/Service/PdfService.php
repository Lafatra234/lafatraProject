<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfService
{
    private $dompdf, $params;

    public function __construct(ParameterBagInterface $params) {

        $this->params = $params;

        $this->dompdf = new Dompdf();

        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Garamond');
        
        $pdfOptions->set('isHtml5ParserEnabled', true);

        $this->dompdf->setOptions($pdfOptions);
    }

    public function showPdf($html, $name)
    {

        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->stream($name . ".pdf", [
            'Attachment' => false
        ]);
        
    }

    public function generateBinaryPdf($html, $name)
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $output = $this->dompdf->output();

        $path = $this->params->get('pdf_directory');
        $pdfFilepath =  $path . $name . '.pdf';
        
        file_put_contents($pdfFilepath, $output);
    }

    public function deletePdf(string $fic)
    {
        $path = $this->params->get('pdf_directory');
        $pdfFilepath =  $path . $fic . '.pdf';
        unlink($pdfFilepath);
    }
}