<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\UuidTrait;
use App\Repository\StationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: StationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
)]
class Station
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    private string $name;

    #[ORM\OneToOne(targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Address $address;

    #[ORM\OneToMany(targetEntity: CurrentPrice::class, mappedBy: 'station')]
    private Collection $currentPrices;

    #[ORM\OneToMany(targetEntity: PriceHistory::class, mappedBy: 'station')]
    private Collection $priceHistories;

    public function __construct()
    {
        $this->currentPrices = new ArrayCollection();
        $this->priceHistories = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, CurrentPrice>
     */
    public function getCurrentPrices(): Collection
    {
        return $this->currentPrices;
    }

    public function addCurrentPrice(CurrentPrice $currentPrice): static
    {
        if (!$this->currentPrices->contains($currentPrice)) {
            $this->currentPrices->add($currentPrice);
            $currentPrice->setStation($this);
        }

        return $this;
    }

    public function removeCurrentPrice(CurrentPrice $currentPrice): static
    {
        if ($this->currentPrices->removeElement($currentPrice)) {
            if ($currentPrice->getStation() === $this) {
                $currentPrice->setStation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PriceHistory>
     */
    public function getPriceHistories(): Collection
    {
        return $this->priceHistories;
    }

    public function addPriceHistory(PriceHistory $priceHistory): static
    {
        if (!$this->priceHistories->contains($priceHistory)) {
            $this->priceHistories->add($priceHistory);
            $priceHistory->setStation($this);
        }

        return $this;
    }

    public function removePriceHistory(PriceHistory $priceHistory): static
    {
        if ($this->priceHistories->removeElement($priceHistory)) {
            if ($priceHistory->getStation() === $this) {
                $priceHistory->setStation(null);
            }
        }

        return $this;
    }
}
