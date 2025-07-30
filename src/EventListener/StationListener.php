<?php

namespace App\EventListener;

use App\Application\Command\GetGooglePlaceSearchNearby;
use App\Dto\MessageBus;
use App\Entity\Station;
use App\Service\MessageBusService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsDoctrineListener(event: Events::postPersist)]
final class StationListener
{
    public function __construct(
        private MessageBusService $bus,
    ) {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $entity = $postPersistEventArgs->getObject();
        if (!$entity instanceof Station) {
            return;
        }

        // $this->bus->dispatch(
        //     messages: [
        //         new MessageBus(
        //             command: new GetGooglePlaceSearchNearby(
        //                 id: $entity->getId(),
        //             ),
        //             stamp: new AmqpStamp('async-medium'),
        //         )
        //     ],
        // );
    }
}
