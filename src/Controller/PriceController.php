<?php

namespace App\Controller;

use App\Dto\Context;
use App\Entity\Station;
use App\Entity\Type;
use App\Repository\PriceHistoryRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class PriceController extends AbstractController
{
    public function __construct(
        private readonly PriceHistoryRepository $priceHistoryRepository,
        private readonly SerializerInterface $serializer,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    #[Route('/prices/history/{stationId}/{typeId}/{year}', name: 'stations_prices_history', methods: ['GET'])]
    public function pricesHistory(
        #[MapEntity(mapping: ['stationId' => 'id'])]
        Station $station,
        #[MapEntity(mapping: ['typeId' => 'id'])]
        Type $type,
        int $year,
        Context $context,
    ): JsonResponse {
        $data = $this->priceHistoryRepository->findByStationAndTypeAndYear($station, $type, $year);

        return new JsonResponse(
            data: $this->serializer->serialize($data, 'json', $context->getGroups()),
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
