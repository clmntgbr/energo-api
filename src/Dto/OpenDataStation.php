<?php

namespace App\Dto;

class OpenDataStation
{
    public function __construct(
        public string $id,
        public float $latitude,
        public float $longitude,
        public string $postalCode,
        public string $pop,
        public string $address,
        public string $city,
        /** @var string[] */
        public array $services = [],
        /** @var Price[] */
        public array $prices = [],
        /** @var array[] */
        public array $hours = [],
    ) {
    }
}
