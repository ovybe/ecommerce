<?php

namespace App\Repository;

use App\Entity\Memory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Memory>
 *
 * @method Memory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Memory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Memory[]    findAll()
 * @method Memory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Memory[]    getNByType($number = 5)
 * @method Memory[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class MemoryRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Memory::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select m.memory_name as filter_name, m.memory_count as filter_count, m.memory_column as filter_column
                from(
                select memtype as memory_name, count(memtype) as memory_count, "memtype" as memory_column
                from memory
                group by memory_name
                union
                select capacity as memory_name, count(capacity) as memory_count, "capacity" as memory_column
                from memory
                group by memory_name
                union
                select frequency as memory_name, count(frequency) as memory_count, "frequency" as memory_column
                from memory
                group by memory_name
                union
                select latency as memory_name, count(latency) as memory_count, "latency" as memory_column
                from memory
                group by memory_name
                ) as m';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Memory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Memory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Memory[] Returns an array of Memory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Memory
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
