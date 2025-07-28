<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

class GetGooglePlaceDetails implements CommandInterface
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $placeId,
    ) {
    }
}
