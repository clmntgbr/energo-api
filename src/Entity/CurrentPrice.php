<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\CurrentPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CurrentPriceRepository::class)]
class CurrentPrice extends Price
{
    use UuidTrait;

    #[ORM\ManyToOne(targetEntity: Station::class, inversedBy: 'currentPrices')]
    #[ORM\JoinColumn(nullable: false)]
    protected Station $station;

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(?Station $station): static
    {
        $this->station = $station;

        return $this;
    }
}
