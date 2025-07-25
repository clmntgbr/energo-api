<?php

namespace App\Application\Command;

use App\Dto\OpenDataStation;

class CreateOrUpdateGasStation
{
    public function __construct(
        public readonly OpenDataStation $OpenDataStation,
    ) {
    }
}
