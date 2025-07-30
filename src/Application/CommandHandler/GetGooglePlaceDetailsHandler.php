<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGooglePlace;
use App\Application\Command\GetGooglePlaceDetails;
use App\Application\Command\GetTrustStationGooglePlace;
use App\Dto\MessageBus;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\GooglePlaceService;
use App\Service\MessageBusService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsMessageHandler]
class GetGooglePlaceDetailsHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly GooglePlaceService $googlePlaceService,
        private readonly MessageBusService $bus,
        private readonly LoggerInterface $logger,
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
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to get place details from Google Places API.', [
                'stationId' => $station->getId(),
                'placeId' => $message->placeId,
                'exception' => $exception->getMessage(),
            ]);

            $station->markAsPlaceDetailsFailed();
            $this->stationRepository->save($station);

            return;
        }

        $this->bus->dispatch(
            messages: [
                new MessageBus(
                    command: new CreateGooglePlace(
                        stationId: $station->getId(),
                        placeDetails: $placeDetails,
                    ),
                    stamp: new AmqpStamp('async-high'),
                ),
                new MessageBus(
                    command: new GetTrustStationGooglePlace(
                        id: $station->getId(),
                    ),
                    stamp: new AmqpStamp('async-low'),
                ),
            ],
        );
    }
}
