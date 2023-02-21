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
 */
class GpuRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gpu::class);
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
