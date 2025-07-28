<?php

namespace App\EventListener;

use App\Application\Command\GetGooglePlaceSearchNearby;
use App\Entity\GooglePlace;
use App\Repository\GooglePlaceRepository;
use App\Repository\StationRepository;
use App\Service\MessageBusService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsDoctrineListener(event: Events::postPersist)]
final class GooglePlaceListener
{
    public function __construct(
        private readonly GooglePlaceRepository $googlePlaceRepository,
        private readonly StationRepository $stationRepository,
    ) {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $entity = $postPersistEventArgs->getObject();
        if (!$entity instanceof GooglePlace) {
            return;
        }

        /** @var GooglePlace[] $googlePlaces */
        $googlePlaces = $this->googlePlaceRepository->findBy(['placeId' => $entity->getPlaceId()]);

        if (count($googlePlaces) === 1) {
            return;
        }

        foreach ($googlePlaces as $googlePlace) {
            $station = $googlePlace->getStation();
            if (null === $station) {
                continue;
            }

            $station->markAsDuplicateDetected();
            $this->stationRepository->save($station, true);
        }
    }
}
