<?php

namespace App\Repository;

use App\Entity\Cooler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Cooler>
 *
 * @method Cooler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cooler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cooler[]    findAll()
 * @method Cooler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Cooler[]    findAllByValue($value = null)
 * @method Cooler[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class CoolerRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cooler::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select cl.cooler_name as filter_name, cl.cooler_count as filter_count, cl.cooler_column as filter_column
                from(
                    select ctype as cooler_name, count(ctype) as cooler_count, "ctype" as cooler_column
                    from cooler
                    group by cooler_name
                    union
                    select cooling as cooler_name, count(cooling) as cooler_count, "cooling" as cooler_column
                    from cooler
                    group by cooler_name
                    union
                    select height as cooler_name, count(height) as cooler_count, "height" as cooler_column
                    from cooler
                    group by cooler_name
                    union
                    select vents as cooler_name, count(vents) as cooler_count, "vents" as cooler_column
                    from cooler
                    group by cooler_name
                    union
                    select size as cooler_name, count(size) as cooler_count, "size" as cooler_column
                    from cooler
                    group by cooler_name
                ) as cl';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Cooler $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cooler $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Cooler[] Returns an array of Cooler objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cooler
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
