<?php

namespace App\Repository;

use App\Entity\CurrentPrice;
use Doctrine\Persistence\ManagerRegistry;

class CurrentPriceRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrentPrice::class);
    }
}
