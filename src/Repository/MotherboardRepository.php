<?php

namespace App\Repository;

use App\Entity\Motherboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseProductRepository<Motherboard>
 *
 * @method Motherboard|null find($id, $lockMode = null, $lockVersion = null)
 * @method Motherboard|null findOneBy(array $criteria, array $orderBy = null)
 * @method Motherboard[]    findAll()
 * @method Motherboard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Motherboard[]    findAllByValue($value = null)
 * @method Motherboard[]    findAllByStatusAndQuantity(int $status, int $max)
 */
class MotherboardRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Motherboard::class);
    }

    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                union
                select mb.motherboard_name as filter_name, mb.motherboard_count as filter_count, mb.motherboard_column as filter_column
                from(
                select format as motherboard_name, count(format) as motherboard_count, "format" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select cpusocket as motherboard_name, count(cpusocket) as motherboard_count, "cpusocket" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select chipset as motherboard_name, count(chipset) as motherboard_count, "chipset" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select modelchipset as motherboard_name, count(modelchipset) as motherboard_count, "modelchipset" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select interface as motherboard_name, count(interface) as motherboard_count, "interface" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select memory as motherboard_name, count(memory) as motherboard_count, "memory" as motherboard_column
                from motherboard
                group by motherboard_name
                union
                select tech as motherboard_name, count(tech) as motherboard_count, "tech" as motherboard_column
                from motherboard
                group by motherboard_name
                ) as mb';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }

    public function save(Motherboard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Motherboard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Motherboard[] Returns an array of Motherboard objects
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

//    public function findOneBySomeField($value): ?Motherboard
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
