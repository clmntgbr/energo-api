<?php

namespace App\Dto;

use App\Dto\OpenDataPrice;

class OpenDataStation implements \JsonSerializable
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
        /** @var OpenDataPrice[] */
        public array $prices = [],
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'postalCode' => $this->postalCode,
            'pop' => $this->pop,
            'address' => $this->address,
            'city' => $this->city,
            'services' => $this->services,
            'prices' => $this->prices,
        ];
    }
}
