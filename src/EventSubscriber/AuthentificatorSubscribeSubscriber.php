<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthentificatorSubscribeSubscriber implements EventSubscriberInterface
{
    public function onSecurityAuthentificationSuccess($event): void
    {
        dd($event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.authentification.success' => 'onSecurityAuthentificationSuccess',
        ];
    }
}
