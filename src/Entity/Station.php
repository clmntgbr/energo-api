<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\OpenDataStation;
use App\Entity\Trait\UuidTrait;
use App\Enum\StationStatus;
use App\Repository\StationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['station:read']],
        ),
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['station:read']],
        ),
    ],
)]
class Station
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read'])]
    private string $stationId;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read'])]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read'])]
    private string $pop;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read'])]
    private string $status;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['station:read'])]
    private array $services;

    #[ORM\OneToOne(targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['station:read'])]
    private Address $address;

    #[ORM\OneToMany(targetEntity: CurrentPrice::class, mappedBy: 'station')]
    #[ORM\OrderBy(['date' => 'DESC'])]
    #[Groups(['station:read'])]
    private Collection $currentPrices;

    #[ORM\OneToMany(targetEntity: PriceHistory::class, mappedBy: 'station')]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $priceHistories;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->status = StationStatus::IMPORTED->getValue();
        $this->currentPrices = new ArrayCollection();
        $this->priceHistories = new ArrayCollection();
    }

    #[Groups(['station:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    #[Groups(['station:read'])]
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['station:read'])]
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public static function createGasStation(OpenDataStation $OpenDataStation): self
    {
        $station = new self();
        $station->setStationId($OpenDataStation->id);
        $station->setName($OpenDataStation->address);
        $station->setPop($OpenDataStation->pop);
        $station->setAddress(Address::fromDto($OpenDataStation));

        return $station;
    }

    public function getPriceByTypeId(string $typeId): ?CurrentPrice
    {
        $currentPrice = $this->currentPrices->filter(fn (CurrentPrice $currentPrice) => $currentPrice->getType()->getTypeId() === $typeId)->first();
        if (false === $currentPrice) {
            return null;
        }

        return $currentPrice;
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

    public function getStationId(): ?string
    {
        return $this->stationId;
    }

    public function setStationId(string $stationId): static
    {
        $this->stationId = $stationId;

        return $this;
    }

    public function getPop(): ?string
    {
        return $this->pop;
    }

    public function setPop(string $pop): static
    {
        $this->pop = $pop;

        return $this;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function updateServices(array $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function setServices(array $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
