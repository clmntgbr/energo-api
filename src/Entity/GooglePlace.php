<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\UuidTrait;
use App\Repository\GooglePlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GooglePlaceRepository::class)]
#[ApiResource(
    operations: [],
)]
class GooglePlace
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['station:read:full'])]
    private string $placeId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?string $internationalPhoneNumber = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?float $rating = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?float $userRatingCount = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['station:read:full'])]
    private string $businessStatus;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?string $websiteUri = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['station:read:full'])]
    private ?string $googleMapsDirectionsUri = null;

    #[ORM\Column(type: Types::TEXT, nullable: true  )]
    #[Groups(['station:read:full'])]
    private ?string $googleMapsPlaceUri = null;

    #[ORM\Column(type: Types::JSON)]
    private array $placeDetails = [];

    #[ORM\OneToOne(targetEntity: Station::class, mappedBy: 'googlePlace')]
    private Station $station;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    #[Groups(['station:read:full'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPlaceId(): ?string
    {
        return $this->placeId;
    }

    public function setPlaceId(string $placeId): static
    {
        $this->placeId = $placeId;

        return $this;
    }

    public function getInternationalPhoneNumber(): ?string
    {
        return $this->internationalPhoneNumber;
    }

    public function setInternationalPhoneNumber(?string $internationalPhoneNumber = null): static
    {
        $this->internationalPhoneNumber = $internationalPhoneNumber;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating = null): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getUserRatingCount(): ?float
    {
        return $this->userRatingCount;
    }

    public function setUserRatingCount(?float $userRatingCount = null): static
    {
        $this->userRatingCount = $userRatingCount;

        return $this;
    }

    public function getBusinessStatus(): ?string
    {
        return $this->businessStatus;
    }

    public function setBusinessStatus(?string $businessStatus = null): static
    {
        $this->businessStatus = $businessStatus;

        return $this;
    }

    public function getWebsiteUri(): ?string
    {
        return $this->websiteUri;
    }

    public function setWebsiteUri(?string $websiteUri = null): static
    {
        $this->websiteUri = $websiteUri;

        return $this;
    }

    public function getGoogleMapsDirectionsUri(): ?string
    {
        return $this->googleMapsDirectionsUri;
    }

    public function setGoogleMapsDirectionsUri(?string $googleMapsDirectionsUri = null): static
    {
        $this->googleMapsDirectionsUri = $googleMapsDirectionsUri;

        return $this;
    }

    public function getGoogleMapsPlaceUri(): ?string
    {
        return $this->googleMapsPlaceUri;
    }

    public function setGoogleMapsPlaceUri(?string $googleMapsPlaceUri = null): static
    {
        $this->googleMapsPlaceUri = $googleMapsPlaceUri;

        return $this;
    }

    public function getPlaceDetails(): array
    {
        return $this->placeDetails;
    }

    public function setPlaceDetails(array $placeDetails = []): static
    {
        $this->placeDetails = $placeDetails;

        return $this;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(?Station $station = null): static
    {
        $this->station = $station;

        return $this;
    }
}
