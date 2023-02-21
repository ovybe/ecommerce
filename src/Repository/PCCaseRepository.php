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
