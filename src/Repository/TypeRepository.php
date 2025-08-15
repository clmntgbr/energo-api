<?php

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Persistence\ManagerRegistry;

class TypeRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function getTypeIds(): array
    {
        /** @var Type[] $entities */
        $entities = $this->createQueryBuilder('ft')
            ->select('ft')
            ->getQuery()
            ->getResult();

        return array_map(function ($entity) {
            return (string) $entity->getId();
        }, $entities);
    }
}
