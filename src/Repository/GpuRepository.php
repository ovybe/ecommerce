<?php

namespace App\Repository;

use App\Entity\Gpu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Gpu>
 *
 * @method Gpu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gpu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gpu[]    findAll()
 * @method Gpu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Gpu[]    findAllByValue($value = null)
 * @method Gpu[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class GpuRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gpu::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
        FROM product p
        union
        select g.gpu_name as filter_name, g.gpu_count as filter_count, g.gpu_column as filter_column
        from (
        select interface as gpu_name, count(interface) as gpu_count, "interface" as gpu_column
        from gpu
        group by gpu_name
        union
        select clock as gpu_name, count(clock) as gpu_count, "clock" as gpu_column
        from gpu
        group by gpu_name
        union
        select memory as gpu_name, count(memory) as gpu_count, "memory" as gpu_column
        from gpu
        group by gpu_name
        union
        select size as gpu_name, count(size) as gpu_count, "size" as gpu_column
        from gpu
        group by gpu_name
        union
        select series as gpu_name, count(series) as gpu_count, "series" as gpu_column
        from gpu
        group by gpu_name
        ) as g';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Gpu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Gpu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Gpu[] Returns an array of Gpu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Gpu
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
