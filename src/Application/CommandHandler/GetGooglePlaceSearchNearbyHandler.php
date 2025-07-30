<?php

namespace App\Application\CommandHandler;

use App\Application\Command\GetGooglePlaceDetails;
use App\Application\Command\GetGooglePlaceSearchNearby;
use App\Dto\MessageBus;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\GooglePlaceService;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsMessageHandler]
class GetGooglePlaceSearchNearbyHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly GooglePlaceService $googlePlaceService,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(GetGooglePlaceSearchNearby $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->id]);

        if (null === $station) {
            return;
        }

        try {
            $placeSearchNearby = $this->googlePlaceService->getPlaceSearchNearby($station);
        } catch (\Throwable) {
            $station->markAsPlaceSearchNearbyFailed();
            $this->stationRepository->save($station);

            return;
        }

        $station->markAsPlaceSearchNearbySuccess();
        $this->stationRepository->save($station);

        $this->bus->dispatch(
            messages: [
                new MessageBus(
                    command: new GetGooglePlaceDetails(
                        id: $station->getId(),
                        placeId: $placeSearchNearby->id,
                    ),
                    stamp: new AmqpStamp('async-medium'),
                ),
            ],
        );
    }
}
