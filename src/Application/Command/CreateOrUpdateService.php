<?php

namespace App\Application\Command;

class CreateOrUpdateService implements CommandInterface
{
    public function __construct(
        public readonly string $stationId,
        public readonly string $name,
    ) {
    }
}
