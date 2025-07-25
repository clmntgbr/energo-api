<?php

namespace App\Application\CommandHandler;

use App\Application\Command\GetTrustStationGooglePlace;
use App\Entity\Station;
use App\Repository\CurrentPriceRepository;
use App\Repository\StationRepository;
use App\Repository\TypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetTrustStationGooglePlaceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly CurrentPriceRepository $currentPriceRepository,
        private readonly TypeRepository $typeRepository,
    ) {
    }

    public function __invoke(GetTrustStationGooglePlace $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->id]);

        if (null === $station) {

            return;
        }

        dd('get trust station google place');
    }
}
