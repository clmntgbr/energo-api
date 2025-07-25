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
}
