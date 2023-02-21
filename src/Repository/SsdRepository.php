<?php

namespace App\Repository;

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
 */
class SsdRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ssd::class);
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
