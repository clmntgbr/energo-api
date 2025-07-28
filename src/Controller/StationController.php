<?php

namespace App\Controller;

use App\Dto\Context;
use App\Dto\GeolocationStations;
use App\Dto\GeolocationStationsParameters;
use App\Repository\StationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api", name: "api_")]
class StationController extends AbstractController
{
    const LATITUDE_DEFAULT = 48.8566;
    const LONGITUDE_DEFAULT = 2.3522;

    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly SerializerInterface $serializer,
        private readonly DenormalizerInterface $denormalizer
    ) {
    }

    #[Route("/geolocation/stations", name: "stations_geolocation", methods: ['GET'])]
    public function geolocation(
        #[MapQueryString()] GeolocationStationsParameters $geolocationStationsParameters,
        Context $context
    ): JsonResponse
    {
        $stations = $this->stationRepository->findStationsWithinRadius(
            $geolocationStationsParameters->latitude ?? self::LATITUDE_DEFAULT,
            $geolocationStationsParameters->longitude ?? self::LONGITUDE_DEFAULT,
            $geolocationStationsParameters->radius
        );

        return new JsonResponse(
            data: $this->serializer->serialize($stations, 'json', $context->getGroups()),
            status: Response::HTTP_OK,
            json: true
        );
    }
}