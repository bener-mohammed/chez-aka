<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('c.displayOrder', 'ASC')
            ->addOrderBy('p.displayOrder', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableForPublicMenu(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('c.isActive = :active')
            ->setParameter('available', true)
            ->setParameter('active', true)
            ->orderBy('c.displayOrder', 'ASC')
            ->addOrderBy('p.displayOrder', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableForClickCollect(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.isClickCollect = :clickCollect')
            ->andWhere('c.isActive = :active')
            ->setParameter('available', true)
            ->setParameter('clickCollect', true)
            ->setParameter('active', true)
            ->orderBy('c.displayOrder', 'ASC')
            ->addOrderBy('p.displayOrder', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneAvailableForClickCollect(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->addSelect('c')
            ->andWhere('p.id = :id')
            ->andWhere('p.isAvailable = :available')
            ->andWhere('p.isClickCollect = :clickCollect')
            ->andWhere('c.isActive = :active')
            ->setParameter('id', $id)
            ->setParameter('available', true)
            ->setParameter('clickCollect', true)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
