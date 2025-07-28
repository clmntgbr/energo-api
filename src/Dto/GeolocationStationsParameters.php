<?php

namespace App\Dto;

class GeolocationStationsParameters
{
    public function __construct(
        public int $radius = 500,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public array $types = [],
    ) {
    }
}
