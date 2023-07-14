<?php

namespace App\Repository;

use App\Entity\Cpu;
use App\Entity\Gpu;
use App\Entity\Option;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Flex\Options;

/**
 * @extends BaseProductRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Product[]    findAllByValue($value = null)
 * @method Product[]    findAllByType()
 */
class ProductRepository extends BaseProductRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFiltersAndFunctionValue($filter_arr,$function_type,$function_value){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->leftJoin(Option::class, 'o',Join::WITH, 'p.id=o.product');
        if($function_type==0 && $function_value>0){
            $qb->andWhere('p.category=:category_id');
            $qb->setParameter('category_id',$function_value);
        }
        else if($function_type==1) {
            $any_value="%".$function_value."%";
            $qb->andWhere('p.SKU like :sku or p.shortDesc like :short_desc or p.description like :description or p.name like :name');
            $qb->setParameter('sku',$any_value);
            $qb->setParameter('short_desc',$any_value);
            $qb->setParameter('description',$any_value);
            $qb->setParameter('name',$any_value);
        }


        foreach ($filter_arr as $key => $filters) {
            $qb->andWhere('o.option_name like :filter_key and o.option_value in (:filter_values)');
            $qb->setParameter('filter_key',$key);
            $qb->setParameter('filter_values',$filters);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
