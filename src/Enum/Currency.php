<?php

declare(strict_types=1);

namespace App\Enum;

enum Currency: string
{
    case EUR = 'EUR';

    public function getId(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
