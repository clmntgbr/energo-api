<?php

namespace App\Dto;

class GeolocationStationsParameters
{
    public const LATITUDE_DEFAULT = 48.8566;
    public const LONGITUDE_DEFAULT = 2.3522;

    public function __construct(
        public float $radius = 500,
        public ?float $latitude = self::LATITUDE_DEFAULT,
        public ?float $longitude = self::LONGITUDE_DEFAULT,
        /** @var string[] */
        public array $typeIds = [],
        /** @var string[] */
        public array $serviceIds = [],
    ) {
    }
}
