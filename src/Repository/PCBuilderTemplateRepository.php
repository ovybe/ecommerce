<?php

namespace App\Repository;

use App\Entity\PCBuilderTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PCBuilderTemplate>
 *
 * @method PCBuilderTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PCBuilderTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PCBuilderTemplate[]    findAll()
 * @method PCBuilderTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PCBuilderTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PCBuilderTemplate::class);
    }

    public function save(PCBuilderTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PCBuilderTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PCBuilderTemplate[] Returns an array of PCBuilderTemplate objects
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

//    public function findOneBySomeField($value): ?PCBuilderTemplate
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
