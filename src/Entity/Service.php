<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\UuidTrait;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false],
            paginationEnabled: false,
        ),
    ],
)]
class Service
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['station:read:full', 'service:read'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['station:read:full', 'service:read'])]
    private string $slug;

    #[ORM\ManyToMany(targetEntity: Station::class, mappedBy: 'services')]
    private Collection $stations;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->stations = new ArrayCollection();
    }

    #[Groups(['station:read:full', 'service:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public static function createService(string $name): self
    {
        $service = new self();
        $service->setName($name);

        return $service;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Station>
     */
    public function getStations(): Collection
    {
        return $this->stations;
    }

    public function addStation(Station $station): static
    {
        if (!$this->stations->contains($station)) {
            $this->stations->add($station);
            $station->addService($this);
        }

        return $this;
    }

    public function removeStation(Station $station): static
    {
        if ($this->stations->removeElement($station)) {
            $station->removeService($this);
        }

        return $this;
    }
}
