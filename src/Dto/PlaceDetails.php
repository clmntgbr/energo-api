<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class PlaceDetails
{
    public function __construct(
        #[SerializedPath('[id]')]
        public ?string $id = null,
        #[SerializedPath('[displayName][text]')]
        public ?string $displayName = null,
    ) {
    }
}
