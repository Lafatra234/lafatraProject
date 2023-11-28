<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\PdfService;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'app_purchase')]
    public function purchas(SessionInterface $session, TicketRepository $ticketRepository, 
    EntityManagerInterface $em, EventRepository $eventRepository, PdfService $pdf, 
    QrCodeService $qrCodeService, MailerService $mail, UserRepository $userRepository)
    {
        

        $panier = $session->get('panier', []);
        $number = 0;

        foreach($panier as $id => $quantity){

            
            $ticket = $ticketRepository->find($id);
            $event_id = $ticket->getEvent();
            $event = $eventRepository->find($event_id);
            $ref1 = $event->getReference() . '/A' . md5(uniqid()); 

            for($i = 1; $i <= $quantity; $i++){
                $purchase = new Purchase();
                $date = new \DateTimeImmutable('now');
                $purchase->setPurchaseDate($date);
                $purchase->setTicketNumber($ticket);
                $purchase->setTicketNumberPurchased(1);
                $purchase->setUser($this->getUser());
                $ref2 = $ref1 . '/B' . md5(uniqid());
                $purchase->setReference($ref2);
                $beginDate = $event->getBeginDate();
                $formatBegin = $beginDate->format('d-m-Y H:i:s');
                $endDate = $event->getEndDate();
                $formatEnd = $endDate->format('d-m-Y H:i:s');
                $qrCode = $qrCodeService->makeQr($ref2);
                $simple = $qrCode['simple'];
                $html = $this->renderView('_partials/makePdf.html.twig', compact('formatBegin', 'simple', 'formatEnd', 'event'));
                $user = $this->getUser();
                $userAct = $userRepository->find($user);
                $pdfName = 'Billet_pour_'. $event->getEventName() . '_' . $ticket->getTicketType(). '_' . $i;
                $pdf->generateBinaryPdf($html, $pdfName);
                $mail->send(
                    'no-reply@epurchase.net',
                    $userAct->getEmail(),
                    'Tester mon envoi de mail',
                    $pdfName . '.pdf'
                );
                $pdf->deletePdf($pdfName);
                $em->persist($purchase);
                $em->flush();
            }
        }

        return new Response('Succès');

    }

    #[Route('/achat/create-session-stripe', name: 'stripe_payment')]
    public function StripeCheckout(SessionInterface $session, TicketRepository $ticketRepository,
     UserRepository $userRepository, UrlGeneratorInterface $generator): RedirectResponse
    {
        $panier = $session->get('panier', []);
        $ticketStripe = [];

        foreach($panier as $id => $quantity){
            $ticket = $ticketRepository->find($id);
            $ticketStripe[] = [
                'price_data' => [
                    'currency' => 'mga',
                    'unit_amount' => $ticket->getPrice(),
                    'product_data' => [
                        'name' => $ticket->getTicketType()
                    ]
                ],
                'quantity' => $quantity
            ];
        }

        $user = $userRepository->find($this->getUser());

        Stripe::setApiKey('sk_test_51OBdEaDAlvEqrnMdEFoBBsmzflzNhcq5GVgMJetz776vULBlOTnRVu6mzLEVTA2vd0zorMoaXKCeUmHXoW7edkJx00Y31QF18i');


        $checkout_session = \Stripe\Checkout\Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                $ticketStripe
        ]],
        'mode' => 'payment',
        'success_url' => $generator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $generator->generate('payment_failed', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/achat/success', name: 'payment_success')]
    public function StripeSuccess(EventRepository $eventRepository, SessionInterface $session): Response
    {
        return new RedirectResponse('app_purchase');
    }

    #[Route('/achat/failed', name: 'payment_failed')]
    public function StripeFailed(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('purchase/failed.html.twig', compact('events'));
    }
}
