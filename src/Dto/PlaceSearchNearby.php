<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class PlaceSearchNearby
{
    public function __construct(
        #[SerializedPath('[places][0][id]')]
        public string $id,
    ) {
    }
}
