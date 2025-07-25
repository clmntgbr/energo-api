<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateGasPrice;
use App\Application\Command\CreateOrUpdateGasStation;
use App\Entity\Station;
use App\Repository\StationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateOrUpdateGasStationHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CreateOrUpdateGasStation $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->openDataStation->id]);

        if (null === $station) {
            $station = Station::createGasStation($message->openDataStation);
        }

        $station->updateServices($message->openDataStation->services);
        $station->updateOpenData($message->openDataStation->jsonSerialize());

        foreach ($message->openDataStation->prices as $price) {
            $this->bus->dispatch(new CreateOrUpdateGasPrice(
                stationId: $message->openDataStation->id,
                openDataPrice: $price,
            ), [new AmqpStamp('async-medium')]);
        }

        $this->stationRepository->save($station, true);
    }
}
