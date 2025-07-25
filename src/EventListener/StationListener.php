<?php

namespace App\EventListener;

use App\Application\Command\GetGooglePlaceSearchNearby;
use App\Application\Command\GetTrustStationGooglePlace;
use App\Entity\Station;
use App\Enum\StationStatus;
use App\Service\MessageBusService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
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

        $this->bus->dispatch(
            messages: [
                new GetGooglePlaceSearchNearby(
                    id: $entity->getId(),
                ),
            ],
            stamp: new AmqpStamp('async-low'),
        );
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $entity = $preUpdateEventArgs->getObject();
        if (!$entity instanceof Station) {
            return;
        }

        $status = $preUpdateEventArgs->getNewValue('status');

        if ($status === StationStatus::PLACE_DETAILS_SUCCESS->getValue()) {
            $entity->markAsValidationPending();
            
            $this->bus->dispatch(
                messages: [
                    new GetTrustStationGooglePlace(
                        id: $entity->getId(),
                    ),
                ],
                stamp: new AmqpStamp('async-low'),
            );

            return;
        }
    }
}
