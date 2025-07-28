<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateService;
use App\Entity\Service;
use App\Entity\Station;
use App\Repository\ServiceRepository;
use App\Repository\StationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateOrUpdateServiceHandler
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    public function __invoke(CreateOrUpdateService $message): void
    {
        /** @var ?Station $station */
        $station = $this->stationRepository->findOneBy(['stationId' => $message->stationId]);

        if (null === $station) {
            return;
        }

        $service = $this->serviceRepository->findOneBy(['name' => $message->name]);

        if (null === $service) {
            $service = Service::createService($message->name);
        }

        $station->addService($service);
        $this->stationRepository->save($station, true);
    }
}
