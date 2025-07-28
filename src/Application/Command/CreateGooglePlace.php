<?php

namespace App\Application\Command;

use App\Dto\PlaceDetails;
use Symfony\Component\Uid\Uuid;

class CreateGooglePlace implements CommandInterface
{
    public function __construct(
        public readonly Uuid $stationId,
        public readonly PlaceDetails $placeDetails,
    ) {
    }
}
