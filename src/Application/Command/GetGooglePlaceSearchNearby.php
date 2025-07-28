<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

class GetGooglePlaceSearchNearby implements CommandInterface
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
