<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGooglePlace;
use App\Entity\GooglePlace;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\GooglePlaceService;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGooglePlaceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly GooglePlaceService $googlePlaceService,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(CreateGooglePlace $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $googlePlace = new GooglePlace();
        $googlePlace->setPlaceId($message->placeDetails->id);

        $station->setName($message->placeDetails->displayName);
        $station->setGooglePlace($googlePlace);
        $station->markAsPlaceDetailsSuccess();

        $this->stationRepository->save($station);
    }
}
