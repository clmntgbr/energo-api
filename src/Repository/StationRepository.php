<?php

namespace App\Repository;

use App\Entity\Station;
use Doctrine\Persistence\ManagerRegistry;

class StationRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Station::class);
    }
}
