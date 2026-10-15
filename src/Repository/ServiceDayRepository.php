<?php

namespace App\Repository;

use App\Entity\ServiceDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceDay>
 */
class ServiceDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceDay::class);
    }

    public function createClickCollectAvailableQueryBuilder()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isOpen = :open')
            ->andWhere('s.clickCollectEnabled = :enabled')
            ->andWhere('s.serviceDate >= :today')
            ->setParameter('open', true)
            ->setParameter('enabled', true)
            ->setParameter(
                'today',
                new \DateTimeImmutable('today')
            )
            ->orderBy('s.serviceDate', 'ASC');
    }


    //    /**
    //     * @return ServiceDay[] Returns an array of ServiceDay objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ServiceDay
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
