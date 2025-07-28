<?php

namespace App\Application\Command;

use App\Dto\OpenDataPrice;

class CreateOrUpdateService
{
    public function __construct(
        public readonly string $stationId,
        public readonly string $name,
    ) {
    }
}
