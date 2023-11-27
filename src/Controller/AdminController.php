<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/administration', name: 'app_admin')]
    public function show(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('admin/index.html.twig', compact('events'));
    }

    #[Route('/admin/évènement/info/{id}', name: 'event_info')]
    public function show_info(int $id, EventRepository $eventRepository, TicketRepository $ticketRepository): Response
    {

        $event = $eventRepository->find($id);
        $beginDate = $event->getBeginDate();
        $endDate = $event->getEndDate();
        $tickets = $ticketRepository->findBy([
            'event' => $event
        ]);

        return $this->render('admin/info_event.html.twig', compact('event','beginDate','endDate','tickets'));
    }

    #[Route('/event/approuvé/{id}', name: 'event_approved')]
    public function approved(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        $event->setStatutEvent('En cours');
        $entityManager->flush();

        return $this->redirectToRoute('app_admin');

    }

    #[Route('/event/désapprouvé/{id}', name: 'event_desapproved')]
    public function desapproved(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        $event->setStatutEvent('Désapprouvé');
        $entityManager->flush();

        return $this->redirectToRoute('app_admin');

    }
}
