<?php

namespace App\Application\Command;

use App\Dto\OpenDataPrice;

class CreateGasCurrentPrice implements CommandInterface
{
    public function __construct(
        public readonly string $stationId,
        public readonly OpenDataPrice $openDataPrice,
    ) {
    }
}
