<?php

namespace App\Application\Command;

use App\Dto\OpenDataStation;

class CreateOrUpdateGasStation implements CommandInterface
{
    public function __construct(
        public readonly OpenDataStation $openDataStation,
    ) {
    }
}
