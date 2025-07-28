<?php

namespace App\Repository;

use App\Entity\Station;
use App\Enum\StationStatus;
use Doctrine\Persistence\ManagerRegistry;

class StationRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Station::class);
    }

    /**
     * @return array<int, array{station: Station, distance: string}>
     */
    public function findStationsWithinRadius(float $latitude, float $longitude, int $radiusMeters): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s as station')
            ->addSelect('(
                6371000 * acos(
                    cos(radians(:latitude))
                    * cos(radians(a.latitude))
                    * cos(radians(a.longitude) - radians(:longitude))
                    + sin(radians(:latitude))
                    * sin(radians(a.latitude))
                )
            ) AS distance')
            ->innerJoin('s.address', 'a')
            ->where('(
                6371000 * acos(
                    cos(radians(:latitude))
                    * cos(radians(a.latitude))
                    * cos(radians(a.longitude) - radians(:longitude))
                    + sin(radians(:latitude))
                    * sin(radians(a.latitude))
                )
            ) <= :radius')
            ->andWhere('s.status = :status')
            ->orderBy('distance', 'ASC')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('radius', $radiusMeters)
            ->setParameter('status', StationStatus::VALIDATED->getValue());

        return $qb->getQuery()->getResult();
    }
}
