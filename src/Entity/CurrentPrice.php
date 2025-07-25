<?php

namespace App\Entity;

use App\Dto\OpenDataPrice;
use App\Entity\Trait\UuidTrait;
use App\Enum\Currency;
use App\Repository\CurrentPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CurrentPriceRepository::class)]
class CurrentPrice extends Price
{
    use UuidTrait;

    #[ORM\ManyToOne(targetEntity: Station::class, inversedBy: 'currentPrices')]
    #[ORM\JoinColumn(nullable: false)]
    protected Station $station;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    #[Groups(['station:read:full', 'station:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public static function createGasPrice(Station $station, OpenDataPrice $openDataPrice, Type $type): self
    {
        $price = new self();

        $price->setStation($station);
        $price->setCurrency(Currency::EUR->getValue());
        $price->setValue($openDataPrice->value);
        $price->setDate($openDataPrice->updatedAt);
        $price->setType($type);

        return $price;
    }

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
