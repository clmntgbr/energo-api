<?php

namespace App\Application\Command;

use App\Dto\OpenDataPrice;

class CreateOrUpdateGasPrice
{
    public function __construct(
        public readonly string $stationId,
        public readonly OpenDataPrice $openDataPrice,
    ) {
    }
}
