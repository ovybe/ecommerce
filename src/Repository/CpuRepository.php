<?php

namespace App\Repository;

use App\Entity\Cpu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cpu>
 *
 * @method Cpu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cpu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cpu[]    findAll()
 * @method Cpu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Cpu[]    findAllByValue($value = null)
 * @method Cpu[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class CpuRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cpu::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select c.cpu_name as filter_name, c.cpu_count as filter_count, c.cpu_column as filter_column
                from (
                    select socket as cpu_name, count(socket) as cpu_count, "socket" as cpu_column
                    from cpu
                    group by cpu_name
                    union
                    select series as cpu_name, count(series) as cpu_count, "series" as cpu_column
                    from cpu
                    group by cpu_name
                    union
                    select core as cpu_name, count(core) as cpu_count, "core" as cpu_column
                    from cpu
                    group by cpu_name
                    union
                    select frequency as cpu_name, count(frequency) as cpu_count, "frequency" as cpu_column
                    from cpu
                    group by cpu_name
                ) as c';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Cpu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cpu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


//    /**
//     * @return Cpu[] Returns an array of Cpu objects
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

//    public function findOneBySomeField($value): ?Cpu
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
