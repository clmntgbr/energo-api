<?php

namespace App\Repository;

use App\Dto\GeolocationStationsParameters;
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
    public function findStationsWithinRadius(GeolocationStationsParameters $geolocationStationsParameters): array
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
            // ->andWhere('s.status = :status')
            ->orderBy('distance', 'ASC')
            ->setMaxResults(200)
            // ->setParameter('status', StationStatus::VALIDATED->getValue())
            ->setParameter('latitude', $geolocationStationsParameters->latitude)
            ->setParameter('longitude', $geolocationStationsParameters->longitude)
            ->setParameter('radius', $geolocationStationsParameters->radius);

        if (!empty($geolocationStationsParameters->typeIds)) {
            $qb->innerJoin('s.currentPrices', 'cp')
                ->innerJoin('cp.type', 't')
                ->andWhere('t.id IN (:typeIds)')
                ->setParameter('typeIds', $geolocationStationsParameters->typeIds);
        }

        if (!empty($geolocationStationsParameters->serviceIds)) {
            $qb->innerJoin('s.services', 'ss')
                ->andWhere('ss.id IN (:serviceIds)')
                ->setParameter('serviceIds', $geolocationStationsParameters->serviceIds);
        }

        return $qb->getQuery()->getResult();
    }
}
