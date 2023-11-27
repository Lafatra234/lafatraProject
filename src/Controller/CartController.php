<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\NumberFormType;
use App\Repository\EventRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    #[Route('/panier/{eventId}', name: 'cart')]
    public function show(SessionInterface $session, TicketRepository $ticketRepository, EventRepository $eventRepository, Request $request, int $eventId): Response
    {
        $panier = $session->get('panier', []);
        
        $data = [];
        $total = 0;

        foreach($panier as $id => $quantity){

            $ticket = $ticketRepository->find($id);
            $event_id = $ticket->getEvent();
            $event = $eventRepository->find($event_id);
            $data[] = [
                'event' => $event->getEventName(),
                'ticketType' => $ticket->getTicketType(),
                'price' => $ticket->getPrice(),
                'quantity' => $quantity,
                'totalPrice' => $ticket->getPrice() * $quantity
            ];
            
            $total += $ticket->getPrice() * $quantity;

        }

        $routeOrigine = $request->attributes->get('_route');

        // Inspectez la route d'origine et appliquez votre condition if ici
        if ($routeOrigine == 'cart') {
            return $this->redirectToRoute('event_detail', ['id' => $eventId]);
        }

        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    #[Route('/panier/{event_id}/{id}/{number}', name: 'app_cart')]
    public function add(Ticket $ticket, SessionInterface $session, int $number, int $event_id): Response
    {

        //on recupère l'id du ticket
        $id = $ticket->getId();

        //on recupère le panier existant
        $panier = $session->get('panier', []);

        //on ajoute dans le panier si ticket non existant ou alors on incrémente
        if(empty($panier[$id])){
            if($number == 0){
                $number;
            }else{
                $panier[$id] = $number;
            }
        }else{
            $panier[$id] += $number;
        }

        $session->set('panier', $panier);

        $user = $this->getUser();

        if($user){
            return $this->redirectToRoute('cart', ['eventId' => $event_id]);
        }else{
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/panier_detail', name: 'cart_detail')]
    public function showDetail(SessionInterface $session, TicketRepository $ticketRepository, EventRepository $eventRepository): Response
    {
        $panier = $session->get('panier', []);
        
        $data = [];
        $total = 0;

        foreach($panier as $id => $quantity){

            $ticket = $ticketRepository->find($id);
            $event_id = $ticket->getEvent();
            $event = $eventRepository->find($event_id);
            $data[] = [
                'event' => $event->getEventName(),
                'ticketType' => $ticket->getTicketType(),
                'price' => $ticket->getPrice(),
                'quantity' => $quantity,
                'totalPrice' => $ticket->getPrice() * $quantity,
                'id' => $ticket->getId()
            ];
            
            $total += $ticket->getPrice() * $quantity;

        }

        return $this->render('cart/cart.html.twig', compact('data', 'total'));
    }

    #[Route('/enlever/{id}', name: 'remove_ticket')]
    public function removeOne(Ticket $ticket, SessionInterface $session): Response
    {

        //on recupère l'id du ticket
        $id = $ticket->getId();

        //on recupère le panier existant
        $panier = $session->get('panier', []);

        //on ajoute dans le panier si ticket non existant ou alors on incrémente
        if(!empty($panier[$id])){
            if($panier[$id] > 1){
                $panier[$id] --;
            }else{
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_detail');
    }

    #[Route('/ajouter/{id}', name: 'add_ticket')]
    public function addMore(Ticket $ticket, SessionInterface $session): Response
    {

        //on recupère l'id du ticket
        $id = $ticket->getId();

        //on recupère le panier existant
        $panier = $session->get('panier', []);

        //on ajoute dans le panier si ticket non existant ou alors on incrémente
        if(!empty($panier[$id])){
            $panier[$id] ++;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_detail');
    }

    #[Route('/supprimer/{id}', name: 'delete_ticket')]
    public function delete(Ticket $ticket, SessionInterface $session): Response
    {

        //on recupère l'id du ticket
        $id = $ticket->getId();

        //on recupère le panier existant
        $panier = $session->get('panier', []);

        //on ajoute dans le panier si ticket non existant ou alors on incrémente
        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_detail');
    }

    #[Route('/vider', name: 'empty_cart')]
    public function empty(SessionInterface $session): Response
    {
        $session->remove('panier');
        return $this->redirectToRoute('cart_detail');
    }
}
