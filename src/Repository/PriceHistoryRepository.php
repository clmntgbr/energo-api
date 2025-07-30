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

    public function findByStationAndTypeAndYear(Station $station, Type $type, int $year): array
    {
        return $this->createQueryBuilder('ph')
            ->where('ph.station = :station')
            // ->andWhere('ph.type = :type')
            ->andWhere('ph.date >= :startDate')
            ->andWhere('ph.date < :endDate')
            ->setParameter('station', $station)
            // ->setParameter('type', $type)
            ->setParameter('startDate', "$year-01-01")
            ->setParameter('endDate', ($year + 1) . "-01-01")
            ->getQuery()
            ->getResult();
    }
}
