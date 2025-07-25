<?php

namespace App\Enum;

enum StationStatus: string
{
    case IMPORTED = 'IMPORTED';
    case GEOCODING_PENDING = 'GEOCODING_PENDING';
    case GEOCODING_SUCCESS = 'GEOCODING_SUCCESS';
    case GEOCODING_FAILED = 'GEOCODING_FAILED';
    case VALIDATION_PENDING = 'VALIDATION_PENDING';
    case VALIDATED = 'VALIDATED';
    case REJECTED = 'REJECTED';
    case ARCHIVED = 'ARCHIVED';

    public function getValue(): string
    {
        return $this->value;
    }
}