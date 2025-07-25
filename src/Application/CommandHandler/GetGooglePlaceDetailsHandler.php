<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGooglePlace;
use App\Application\Command\GetGooglePlaceDetails;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\GooglePlaceService;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsMessageHandler]
class GetGooglePlaceDetailsHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly GooglePlaceService $googlePlaceService,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(GetGooglePlaceDetails $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->id]);

        if (null === $station) {
            return;
        }

        try {
            $placeDetails = $this->googlePlaceService->getPlaceDetails($message->placeId);
        } catch (\Throwable) {
            $station->markAsPlaceDetailsFailed();
            $this->stationRepository->save($station);

            return;
        }

        $this->bus->dispatch(
            messages: [
                new CreateGooglePlace(
                    stationId: $station->getId(),
                    placeDetails: $placeDetails,
                ),
            ],
            stamp: new AmqpStamp('async-low'),
        );
    }
}
