<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGasCurrentPrice;
use App\Application\Command\CreateGasPriceHistory;
use App\Application\Command\CreateOrUpdateGasPrice;
use App\Entity\Station;
use App\Repository\CurrentPriceRepository;
use App\Repository\StationRepository;
use App\Service\MessageBusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateOrUpdateGasPriceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly CurrentPriceRepository $currentPriceRepository,
        private readonly MessageBusService $bus,
    ) {
    }

    public function __invoke(CreateOrUpdateGasPrice $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $price = $station->getPriceByTypeId($message->openDataPrice->id);

        if (null === $price) {
            $this->dispatchPriceMessages($message);

            return;
        }

        if ($price->getDate() < $message->openDataPrice->updatedAt) {
            $this->dispatchPriceMessages($message);
            $this->currentPriceRepository->delete($price);
        }
    }

    private function dispatchPriceMessages(CreateOrUpdateGasPrice $message): void
    {
        $this->bus->dispatch(
            messages: [
                new CreateGasCurrentPrice(
                    stationId: $message->stationId,
                    openDataPrice: $message->openDataPrice
                ),
                new CreateGasPriceHistory(
                    stationId: $message->stationId,
                    openDataPrice: $message->openDataPrice
                ),
            ]
        );
    }
}
