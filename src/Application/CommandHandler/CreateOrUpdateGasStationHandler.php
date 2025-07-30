<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateGasPrice;
use App\Application\Command\CreateOrUpdateGasStation;
use App\Application\Command\CreateOrUpdateService;
use App\Dto\MessageBus;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;

#[AsMessageHandler]
class CreateOrUpdateGasStationHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(CreateOrUpdateGasStation $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->openDataStation->id]);

        if (null === $station) {
            $station = Station::createGasStation($message->openDataStation);
        }

        $station->updateOpenData($message->openDataStation->jsonSerialize());
        $station->clearServices();

        array_map(
            fn ($price) => $this->bus->dispatch(
                messages: [
                    new MessageBus(
                        command: new CreateOrUpdateGasPrice(
                            stationId: $message->openDataStation->id,
                            openDataPrice: $price,
                        ),
                        stamp: new AmqpStamp('async-medium'),
                    ),
                ],
            ),
            $message->openDataStation->prices
        );

        array_map(
            fn ($service) => $this->bus->dispatch(
                messages: [
                    new MessageBus(
                        command: new CreateOrUpdateService(
                            stationId: $message->openDataStation->id,
                            name: $service,
                        ),
                        stamp: new AmqpStamp('async-low'),
                    ),
                ],
            ),
            $message->openDataStation->services
        );

        $this->stationRepository->save($station, true);
    }
}
