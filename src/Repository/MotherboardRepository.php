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
 */
class MotherboardRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Motherboard::class);
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
