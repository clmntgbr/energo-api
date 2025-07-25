<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGasCurrentPrice;
use App\Entity\CurrentPrice;
use App\Entity\Station;
use App\Repository\CurrentPriceRepository;
use App\Repository\StationRepository;
use App\Repository\TypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateGasCurrentPriceHandler extends CreateGasPriceHandlerAbstract
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly CurrentPriceRepository $currentPriceRepository,
        private readonly TypeRepository $typeRepository,
    ) {
        parent::__construct($typeRepository);
    }

    public function __invoke(CreateGasCurrentPrice $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $type = $this->getType($message->openDataPrice);
        $price = CurrentPrice::createGasPrice($station, $message->openDataPrice, $type);
        $this->currentPriceRepository->save($price, true);
    }
}
