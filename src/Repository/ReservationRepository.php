<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ServiceDay;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function countConfirmedPeopleByArea(
        ServiceDay $serviceDay,
        string $area
    ): int {
        $result = $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.partySize), 0)')
            ->andWhere('r.serviceDay = :serviceDay')
            ->andWhere('r.status = :status')
            ->andWhere('r.confirmedArea = :area')
            ->setParameter('serviceDay', $serviceDay)
            ->setParameter('status', 'CONFIRMED')
            ->setParameter('area', $area)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }


    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
