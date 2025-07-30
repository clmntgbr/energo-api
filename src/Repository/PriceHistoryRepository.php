<?php

namespace App\Repository;

use App\Entity\PriceHistory;
use App\Entity\Station;
use App\Entity\Type;
use Doctrine\Persistence\ManagerRegistry;

class PriceHistoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceHistory::class);
    }
}
