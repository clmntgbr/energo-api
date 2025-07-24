<?php

namespace App\Dto;

class Context
{
    public function __construct(
        private array $groups,
    ) {
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}
