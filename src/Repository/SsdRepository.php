<?php

namespace App\Repository;

use App\Entity\ProductInventory;
use App\Entity\Ssd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Ssd>
 *
 * @method Ssd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ssd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ssd[]    findAll()
 * @method Ssd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Ssd[]    findAllByValue($value = null)
 * @method Ssd[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class SsdRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ssd::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select s.ssd_name as filter_name, s.ssd_count as filter_count, s.ssd_column as filter_column
                from(
                    select series as ssd_name, count(series) as ssd_count, "series" as ssd_column
                    from ssd
                    group by ssd_name
                    union
                    select interface as ssd_name, count(interface) as ssd_count, "interface" as ssd_column
                    from ssd
                    group by ssd_name
                    union
                    select capacity as ssd_name, count(capacity) as ssd_count, "capacity" as ssd_column
                    from ssd
                    group by ssd_name
                    union
                    select maxreading as ssd_name, count(maxreading) as ssd_count, "maxreading" as ssd_column
                    from ssd
                    group by ssd_name
                ) as s';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Ssd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ssd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Ssd[] The objects.
     */
    public function findAllByStatusAndQuantitySsd(int $status,int $max,string $drivetype): array
    {
        return $this->createQueryBuilder('c')
            ->select('c')
//            ->from($this->entityClass,'c')
//            ->leftJoin(Product::class,'p','WITH','c.i=p.id')
            ->andWhere('c.status = :status_in')
            ->andWhere('c.drivetype = :drivetype')
            ->setParameter('status_in',$status)
            ->setParameter('drivetype',$drivetype)
            ->leftJoin(ProductInventory::class,'i', 'WITH',
                'c.id = i.product')
            ->groupBy('c.id')
            ->having('SUM(i.quantity)>0')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Ssd[] Returns an array of Ssd objects
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

//    public function findOneBySomeField($value): ?Ssd
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
