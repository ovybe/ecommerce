<?php

namespace App\Repository;

use App\Entity\Psu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Base;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Psu>
 *
 * @method Psu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Psu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Psu[]    findAll()
 * @method Psu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Psu[]    findAllByValue($value = null)
 * @method Psu[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class PsuRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Psu::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select p.psu_name as filter_name, p.psu_count as filter_count, p.psu_column as filter_column
                from(
                select power as psu_name, count(power) as psu_count, "power" as psu_column
                from psu
                group by psu_name
                union
                select pfc as psu_name, count(pfc) as psu_count, "pfc" as psu_column
                from psu
                group by psu_name
                union
                select efficiency as psu_name, count(efficiency) as psu_count, "efficiency" as psu_column
                from psu
                group by psu_name
                union
                select certification as psu_name, count(certification) as psu_count, "certification" as psu_column
                from psu
                group by psu_name
                ) as p';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Psu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Psu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Psu[] Returns an array of Psu objects
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

//    public function findOneBySomeField($value): ?Psu
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
