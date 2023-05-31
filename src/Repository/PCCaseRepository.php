<?php

namespace App\Repository;

use App\Entity\PCCase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<PCCase>
 *
 * @method PCCase|null find($id, $lockMode = null, $lockVersion = null)
 * @method PCCase|null findOneBy(array $criteria, array $orderBy = null)
 * @method PCCase[]    findAll()
 * @method PCCase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method PCCase[]    findAllByValue($value = null)
 */
class PCCaseRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PCCase::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select pc.pccase_name as filter_name, pc.pccase_count as filter_count, pc.pccase_column as filter_column
                from(
                select casetype as pccase_name, count(casetype) as pccase_count, "casetype" as pccase_column
                from pccase
                group by pccase_name
                union
                select height as pccase_name, count(height) as pccase_count, "height" as pccase_column
                from pccase
                group by pccase_name
                union
                select diameter as pccase_name, count(diameter) as pccase_count, "diameter" as pccase_column
                from pccase
                group by pccase_name
                union
                select width as pccase_name, count(width) as pccase_count, "width" as pccase_column
                from pccase
                group by pccase_name
                union
                select slots as pccase_name, count(slots) as pccase_count, "slots" as pccase_column
                from pccase
                group by pccase_name
                ) as pc';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(PCCase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PCCase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PCCase[] Returns an array of PCCase objects
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

//    public function findOneBySomeField($value): ?PCCase
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
