<?php

namespace App\Dto;

class OpenDataPrice
{
    public function __construct(
        public string $name,
        public string $id,
        public \DateTime $updatedAt,
        public float $value,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            id: $data['id'] ?? '',
            updatedAt: new \DateTime($data['updatedAt'] ?? ''),
            value: isset($data['value']) ? (float) $data['value'] : 0.0
        );
    }
}
