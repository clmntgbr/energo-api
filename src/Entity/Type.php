<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\TypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['station:read:full', 'station:read'])]
    private string $typeId;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    #[Groups(['station:read:full'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    #[Groups(['station:read:full'])]
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    #[Groups(['station:read:full'])]
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public static function createType(string $typeId, string $name): self
    {
        $type = new self();

        $type->setTypeId($typeId);
        $type->setName($name);

        return $type;
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

    public function getTypeId(): ?string
    {
        return $this->typeId;
    }

    public function setTypeId(string $typeId): static
    {
        $this->typeId = $typeId;

        return $this;
    }
}
