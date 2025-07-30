<?php

namespace App\Controller;

use App\Dto\Context;
use App\Dto\GeolocationStationsParameters;
use App\Entity\Station;
use App\Entity\Type;
use App\Repository\StationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class StationController extends AbstractController
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly SerializerInterface $serializer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    #[Route('/geolocation/stations', name: 'stations_geolocation', methods: ['GET'])]
    public function geolocation(
        #[MapQueryString()] GeolocationStationsParameters $geolocationStationsParameters,
        Context $context,
    ): JsonResponse {
        $stations = $this->stationRepository->findStationsWithinRadius($geolocationStationsParameters);

        return new JsonResponse(
            data: $this->serializer->serialize($stations, 'json', $context->getGroups()),
            status: Response::HTTP_OK,
            json: true
        );
    }

    #[Route('/prices/historical/{stationId}/{typeId}/{year}', name: 'stations_prices_historical', methods: ['GET'])]
    public function pricesHistorical(
        #[MapEntity(mapping: ['stationId' => 'id'])]
        Station $station,
        #[MapEntity(mapping: ['typeId' => 'id'])]
        Type $type,
        int $year,
        Context $context,
    ): JsonResponse {
        return new JsonResponse(
            data: [],
            status: Response::HTTP_OK,
            json: true
        );
    }

    #[Route('/prices/current/{stationId}', name: 'stations_prices_current', methods: ['GET'])]
    public function pricesCurrent(
        #[MapEntity(mapping: ['stationId' => 'id'])]
        Station $station,
        Context $context,
    ): JsonResponse {
        return new JsonResponse(
            data: $this->serializer->serialize($station->getCurrentPrices(), 'json', $context->getGroups()),
            status: Response::HTTP_OK,
            json: true
        );
    }
}
