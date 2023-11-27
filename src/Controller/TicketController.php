<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketFormType;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class TicketController extends AbstractController
{
    #[Route('/ticket/créer/{id}', name: 'app_ticket')]
    public function index(Request $request, $id, EntityManagerInterface $em, EventRepository $eventRepository, TicketRepository $ticketRepository): Response
    {
        $ticket = new Ticket();
        $event = $eventRepository
            ->find($id);
        $ticket->setEvent($event);
        $ID = $event->getId();
        $tickets = $ticketRepository->findBy([
            'event' => $event
        ]);

        $ticketForm = $this->createForm(TicketFormType::class, $ticket);
        $ticketForm->handleRequest($request);

        if($ticketForm->isSubmitted() && $ticketForm->isValid()){
            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('app_ticket', ['id' => $ID]);
        }

        return $this->render('ticket/index.html.twig', [
            'ticketForm' => $ticketForm->createView(),
            'tickets' => $tickets,
        ]);
    }
}
