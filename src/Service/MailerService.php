<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class MailerService
{
    private $mailer, $params;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }

    public function send($from, $to, $subject,string $file_att): void
    {

        $path = $this->params->get('pdf_directory');
        $pdfFilepath = $path . $file_att;

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)            
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>')
            ->addPart(new DataPart(new File($pdfFilepath)));

        $this->mailer->send($email);
    }
}