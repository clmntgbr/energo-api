<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
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
    paginationItemsPerPage: 10,
    operations: [
        new Get(
            normalizationContext: ['skip_null_values' => false],
        ),
        new GetCollection(
            normalizationContext: ['skip_null_values' => false],
        ),
    ],
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'status' => 'exact',
    ]
)]
class Station
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $stationId;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full'])]
    private string $pop;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full'])]
    private string $status;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['station:read:full'])]
    private array $statuses = [];

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?float $trust = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['station:read:full'])]
    private array $services;

    #[ORM\OneToOne(targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['station:read:full', 'station:read'])]
    private Address $address;

    #[ORM\OneToOne(targetEntity: GooglePlace::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['station:read:full'])]
    private ?GooglePlace $googlePlace = null;

    #[ORM\OneToMany(targetEntity: CurrentPrice::class, mappedBy: 'station')]
    #[ORM\OrderBy(['date' => 'DESC'])]
    #[Groups(['station:read:full', 'station:read'])]
    private Collection $currentPrices;

    #[ORM\OneToMany(targetEntity: PriceHistory::class, mappedBy: 'station')]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $priceHistories;

    #[ORM\Column(type: Types::JSON)]
    private array $openData;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->status = StationStatus::IMPORTED->getValue();
        $this->statuses = [StationStatus::IMPORTED->getValue()];
        $this->currentPrices = new ArrayCollection();
        $this->priceHistories = new ArrayCollection();
    }

    #[Groups(['station:read:full', 'station:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    #[Groups(['station:read:full', 'station:read'])]
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['station:read:full', 'station:read'])]
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function markAsPlaceSearchNearbyFailed(): static
    {
        $this->setStatus(StationStatus::PLACE_SEARCH_NEARBY_FAILED);
        $this->statuses[] = StationStatus::PLACE_SEARCH_NEARBY_FAILED->getValue();

        return $this;
    }

    public function markAsPlaceSearchNearbySuccess(): static
    {
        $this->setStatus(StationStatus::PLACE_SEARCH_NEARBY_SUCCESS);
        $this->statuses[] = StationStatus::PLACE_SEARCH_NEARBY_SUCCESS->getValue();

        return $this;
    }

    public function markAsPlaceDetailsFailed(): static
    {
        $this->setStatus(StationStatus::PLACE_DETAILS_FAILED);
        $this->statuses[] = StationStatus::PLACE_DETAILS_FAILED->getValue();

        return $this;
    }

    public function markAsPlaceDetailsSuccess(): static
    {
        $this->setStatus(StationStatus::PLACE_DETAILS_SUCCESS);
        $this->statuses[] = StationStatus::PLACE_DETAILS_SUCCESS->getValue();

        return $this;
    }

    public function markAsValidationPending(): static
    {
        $this->setStatus(StationStatus::VALIDATION_PENDING);
        $this->statuses[] = StationStatus::VALIDATION_PENDING->getValue();

        return $this;
    }

    public static function createGasStation(OpenDataStation $openDataStation): self
    {
        $station = new self();
        $station->setStationId($openDataStation->id);
        $station->setName($openDataStation->address);
        $station->setPop($openDataStation->pop);
        $station->setAddress(Address::fromDto($openDataStation));

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

    public function setStatus(StationStatus $status): static
    {
        $this->status = $status->getValue();

        return $this;
    }

    public function getGooglePlace(): ?GooglePlace
    {
        return $this->googlePlace;
    }

    public function setGooglePlace(?GooglePlace $googlePlace): static
    {
        $this->googlePlace = $googlePlace;

        return $this;
    }

    public function getOpenData(): array
    {
        return $this->openData;
    }

    public function updateOpenData(array $openData): static
    {
        $this->openData = $openData;

        return $this;
    }

    public function setOpenData(array $openData): static
    {
        $this->openData = $openData;

        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): static
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function getTrust(): ?float
    {
        return $this->trust;
    }

    public function setTrust(?float $trust): static
    {
        $this->trust = $trust;

        return $this;
    }
}
