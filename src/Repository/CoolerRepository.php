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
 */
class CoolerRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cooler::class);
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
