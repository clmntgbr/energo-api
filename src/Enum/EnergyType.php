<?php

namespace App\Enum;

enum EnergyType: string
{
    case GASOLINE_95 = 'SP95';
    case GASOLINE_98 = 'SP98';
    case DIESEL = 'Gazole';
    case E10 = 'E10';
    case E85 = 'E85';
    case GPL = 'GPLc';

    public function getValue(): string
    {
        return $this->value;
    }
}
