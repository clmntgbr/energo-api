<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGasPriceHistory;
use App\Entity\PriceHistory;
use App\Entity\Station;
use App\Repository\PriceHistoryRepository;
use App\Repository\StationRepository;
use App\Repository\TypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGasPriceHistoryHandler extends CreateGasPriceHandlerAbstract
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly PriceHistoryRepository $priceHistoryRepository,
        private readonly TypeRepository $typeRepository,
    ) {
        parent::__construct($typeRepository);
    }

    public function __invoke(CreateGasPriceHistory $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $type = $this->getType($message->openDataPrice);
        $price = PriceHistory::createGasPrice($station, $message->openDataPrice, $type);
        $this->priceHistoryRepository->save($price, true);
    }
}
