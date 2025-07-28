<?php

namespace App\Application\CommandHandler;

use App\Application\Command\GetTrustStationGooglePlace;
use App\Entity\Station;
use App\Repository\CurrentPriceRepository;
use App\Repository\StationRepository;
use App\Repository\TypeRepository;
use App\Service\TrustService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetTrustStationGooglePlaceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly CurrentPriceRepository $currentPriceRepository,
        private readonly TypeRepository $typeRepository,
        private readonly TrustService $trustService,
    ) {
    }

    public function __invoke(GetTrustStationGooglePlace $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['id' => $message->id]);

        if (null === $station) {
            return;
        }

        if (null === $station->getGooglePlace()) {
            return;
        }

        $trust = $this->trustService->getTrust($station);
        $station->setTrust($trust);
        $this->stationRepository->save($station);
    }
}
