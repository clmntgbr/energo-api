<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Dto\OpenDataStation;
use App\Dto\PlaceDetails;
use App\Entity\Trait\UuidTrait;
use App\Repository\AddressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ApiResource(
    operations: [],
)]
class Address
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $street;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $city;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $postalCode;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $country;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['station:read:full', 'station:read'])]
    private float $latitude;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['station:read:full', 'station:read'])]
    private float $longitude;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public static function fromDto(OpenDataStation $openDataStation): self
    {
        $address = new self();
        $address->setStreet($openDataStation->address);
        $address->setCity($openDataStation->city);
        $address->setPostalCode($openDataStation->postalCode);
        $address->setLatitude($openDataStation->latitude);
        $address->setLongitude($openDataStation->longitude);
        $address->setCountry('FR');

        return $address;
    }

    public static function fromPlaceDetails(PlaceDetails $placeDetails): self
    {
        $components = array_reduce($placeDetails->addressComponents, function (array $acc, array $component) {
            $types = $component['types'] ?? [];
            $longText = $component['longText'] ?? '';

            return match (true) {
                in_array('street_number', $types) => [...$acc, 'streetNumber' => $longText],
                in_array('route', $types) => [...$acc, 'route' => $longText],
                in_array('locality', $types) => [...$acc, 'city' => $longText],
                in_array('postal_code', $types) => [...$acc, 'postalCode' => $longText],
                in_array('country', $types) => [...$acc, 'country' => $longText],
                default => $acc,
            };
        }, []);

        return (new self())
            ->setStreet(trim(($components['streetNumber'] ?? '').' '.($components['route'] ?? '')))
            ->setCity($components['city'] ?? '')
            ->setPostalCode($components['postalCode'] ?? '')
            ->setCountry($components['country'] ?? '')
            ->setLatitude($placeDetails->latitude)
            ->setLongitude($placeDetails->longitude);
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
