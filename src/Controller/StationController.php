<?php

namespace App\Controller;

use App\Dto\Context;
use App\Dto\GeolocationStationsParameters;
use App\Repository\StationRepository;
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
}
