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
 */
class PsuRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Psu::class);
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
