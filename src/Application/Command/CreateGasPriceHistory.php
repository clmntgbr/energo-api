<?php

namespace App\Application\Command;

use App\Dto\OpenDataPrice;

class CreateGasPriceHistory
{
    public function __construct(
        public readonly string $stationId,
        public readonly OpenDataPrice $openDataPrice,
    ) {
    }
}
