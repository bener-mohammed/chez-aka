<?php

namespace App\Repository;

use App\Entity\CustomerOrder;
use App\Entity\ServiceDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerOrder>
 */
class CustomerOrderRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct(
            $registry,
            CustomerOrder::class
        );
    }

    public function countForServiceDay(
        ServiceDay $serviceDay
    ): int {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.serviceDay = :serviceDay')
            ->andWhere('o.status != :cancelled')
            ->setParameter('serviceDay', $serviceDay)
            ->setParameter('cancelled', 'CANCELLED')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('s')
            ->addSelect('u')
            ->innerJoin('o.serviceDay', 's')
            ->innerJoin('o.user', 'u')
            ->orderBy('s.serviceDate', 'DESC')
            ->addOrderBy('o.pickupTime', 'ASC')
            ->addOrderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
