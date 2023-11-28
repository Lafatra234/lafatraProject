<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use App\Repository\EventRepository;
use App\Repository\PurchaseRepository;
use App\Repository\TicketRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{

    #[Route('/organisateur', name: 'organisateur_place')]
    public function index(EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        $user = $this->getUser();
        $event = $eventRepository->findBy([
            'organizer' => $user
        ]);
        $number = 0;
        foreach($event as $event_number){
            $number = $number + 1;
        }
        return $this->render('event/index.html.twig', [
            'number' => $number,
        ]);
    }

    #[Route('/évènement/creer', name: 'app_event')]
    public function add(Request $request, EntityManagerInterface $entitymanager, PictureService $pictureService): Response
    {
        $event = new Event();
        $user = $this->getUser();
        $event->setOrganizer($user);
        $event->setStatutEvent('En attente');
        $eventForm = $this->createForm(EventFormType::class, $event);
        $eventForm->handleRequest($request);
        if($eventForm->isSubmitted()){

            $event->setReference('E' . md5(uniqid()));

            $image = $eventForm->get('image')->getData();
            $folder = $event->getReference();
            $name = $event->getReference();
            $fichier = $pictureService->add($image, $folder, 300, 300, $name);

            $entitymanager->persist($event);
            $entitymanager->flush();
            return $this->redirectToRoute('app_ticket', ['id' => $event->getId()]);
        }

        return $this->render('event/add.html.twig', [
            'eventForm' => $eventForm->createView(),
        ]);
    }

    #[Route('/évènement/details/{id}', name: 'event_detail')]
    public function show_detail(int $id, EventRepository $eventRepository, TicketRepository $ticketRepository): Response
    {
        $one_event = $eventRepository->find($id);
        $beginDate = $one_event->getBeginDate();
        $formatBegin = $beginDate->format('d-m-Y H:i:s');
        $endDate = $one_event->getEndDate();
        $formatEnd = $endDate->format('Y-m-d H:i:s');
        $tickets = $ticketRepository->findBy([
            'event' => $one_event
        ]);
        return $this->render('event/event_detail.html.twig', compact('one_event','formatBegin','formatEnd', 'tickets'));
    }

    #[Route('/mes_évènement', name: 'My_Event_detail')]
    public function myEvent(EventRepository $eventRepository, TicketRepository $ticketRepository, PurchaseRepository $purchaseRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy([
            'organizer' => $user
        ]);
        foreach($events as $event){
            $query = $em->createQueryBuilder();
            $participants[$event->getId()] = $query->select('p')->from('App\Entity\Purchase', 'p')
                ->where($query->expr()->like('p.reference', $query->expr()->literal('%' . $event->getReference() . '%')))
                ->getQuery()
                ->getResult();
        }
        return $this->render('event/event_user_create.html.twig', compact('events', 'participants'));
    }

    #[Route('/évènements_terminés', name: 'my_past_event')]
    public function pastEvent(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy([
            'organizer' => $user
        ]);

        return $this->render('event/past_event.html.twig', compact('events'));
    }

    #[Route('/évènements_attents', name: 'my_waiting_event')]
    public function waitingEvent(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy([
            'organizer' => $user
        ]);
        return $this->render('event/waiting_event.html.twig', compact('events'));
    }
}