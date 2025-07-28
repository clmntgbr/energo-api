<?php

namespace App\Enum;

enum StationStatus: string
{
    case IMPORTED = 'IMPORTED';

    case PLACE_SEARCH_NEARBY_SUCCESS = 'PLACE_SEARCH_NEARBY_SUCCESS';
    case PLACE_SEARCH_NEARBY_FAILED = 'PLACE_SEARCH_NEARBY_FAILED';

    case PLACE_DETAILS_SUCCESS = 'PLACE_DETAILS_SUCCESS';
    case PLACE_DETAILS_FAILED = 'PLACE_DETAILS_FAILED';

    case VALIDATION_PENDING = 'VALIDATION_PENDING';

    case VALIDATED = 'VALIDATED';
    case REJECTED = 'REJECTED';

    public function getValue(): string
    {
        return $this->value;
    }
}
