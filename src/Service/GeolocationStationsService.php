<?php

namespace App\Service;

use App\Dto\GeolocationStationsParameters;
use App\Repository\StationRepository;
use App\Repository\TypeRepository;

class GeolocationStationsService
{
    public function __construct(
        private readonly StationRepository $stationRepository,
        private readonly TypeRepository $typeRepository,
    ) {
    }

    /**
     * @return array<int, array{station: Station, distance: string, hasLowPrice: bool, lowPriceUuids: string}>
     */
    public function get(GeolocationStationsParameters $geolocationStationsParameters): array
    {
        if (empty($geolocationStationsParameters->typeIds)) {
            $geolocationStationsParameters->typeIds = $this->typeRepository->getTypeIds();
        }

        $stations = $this->stationRepository->findStationsWithinRadius($geolocationStationsParameters);

        array_map(function ($typeId) use (&$stations) {
            $lowestPrice = null;
            $stationIndexWithLowestPrice = null;
            
            array_walk($stations, function ($station, $index) use ($typeId, &$lowestPrice, &$stationIndexWithLowestPrice) {
                $currentPrice = $station['station']->getCurrentPriceByTypeUuid($typeId);
                if ($currentPrice !== null) {
                    $currentValue = $currentPrice->getValue();
                    
                    if ($lowestPrice === null || $currentValue < $lowestPrice) {
                        $lowestPrice = $currentValue;
                        $stationIndexWithLowestPrice = $index;
                    }
                }
            });

            if ($stationIndexWithLowestPrice !== null) {
                $stations[$stationIndexWithLowestPrice]['lowPriceUuids'] .= $typeId . ',';
                $stations[$stationIndexWithLowestPrice]['hasLowPrice'] = 1;
            }

        }, $geolocationStationsParameters->typeIds);

        return $stations;
    }
}