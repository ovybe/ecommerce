<?php

namespace App\Repository;

use App\Entity\Option;
use App\Entity\Product;
use App\Entity\ProductInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Param;

abstract class BaseProductRepository extends ServiceEntityRepository
{
    protected string $entityClass;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $this->entityClass = $entityClass;

        parent::__construct($registry, $entityClass);
    }

    /**
     * @return object[] The objects.
     */
    public function findAllByValue($value): array
    {
        return $this->createQueryBuilder('c')
            ->orWhere('c.name LIKE :name')
            ->orWhere('c.shortDesc LIKE :shortDesc')
            ->orWhere('c.description LIKE :desc')
            ->orWhere('c.SKU LIKE :sku')
            ->setParameters(new ArrayCollection([
                new Parameter('name', '%'.$value.'%'),
                new Parameter('shortDesc','%'.$value.'%'),
                new Parameter('desc', '%'.$value.'%'),
                new Parameter('sku', '%'.$value.'%')
            ]))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
    /**
     * @return object[] The objects.
     */
    public function findFiltersByValue($value): array
    {
        $sql='
                select o.option_name,o.option_value,count(o.option_value) as option_count
                from product p
                left join option o
                on p.id=o.product_id
                where p.sku like :sku or p.short_desc like :short_desc or p.description like :description or p.name like :name
                group by o.option_value
                order by o.option_name ASC
             ';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        $any_value="%".$value."%";
        $stmt->bindValue("sku",$any_value);
        $stmt->bindValue("short_desc",$any_value);
        $stmt->bindValue("description",$any_value);
        $stmt->bindValue("name",$any_value);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findFiltersByCategoryId($category_id){
        $sql='
                select o.option_name,o.option_value,count(o.option_value) as option_count
                from product p
                left join option o
                on p.id=o.product_id
                where p.category_id=:category_id
                group by o.option_value
                order by o.option_name ASC
             ';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue("category_id",$category_id);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * @return object[] The objects.
     */
    public function findAmountByStatusAndQuantity(int $status,int $max): array
    {
        $sql = 'SELECT p.id,p.name,p.description,p.sku,p.thumbnail,p.seller,p.created_at,p.category_id,p.price,p.status,p.uid,p.short_desc FROM
                (
                SELECT p.*,   
                        ROW_NUMBER() OVER(PARTITION BY category_id) AS row_num
                FROM product p where p.status=:status
                               ) as p
                left join product_inventory i ON p.id=i.product_id
                left join option o on p.id=o.product_id                                                   
                where p.row_num<:quantity
                group by p.id
                having sum(i.quantity)>0
                order by p.category_id asc'
        ;
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');
        #$rsm->addJoinedEntityFromClassMetadata(Option::class, 'o', 'p', 'options', array('id' => 'product_id'));
        $stmt = $this->getEntityManager()->createNativeQuery($sql,$rsm);

        $stmt->setParameter('status',$status,ParameterType::INTEGER);
        $stmt->setParameter('quantity',$max, ParameterType::INTEGER);

        return $stmt->getResult();
    }
    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'select o.option_name,o.option_value,count(o.option_value) as option_count
                from option o
                group by o.option_value
                order by o.option_name ASC
                ';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

    }
    /**
     * @return object[] The objects.
     */
    public function findItemsInCategoryUnderPrice($category_id,$price,$filters=[]): array
    {
        $query= $this->createQueryBuilder('c')
            ->leftJoin(Option::class, 'o',Join::WITH, 'c.id=o.product')
            ->where('c.category=:category')
            ->andWhere('c.price<=:price')
            ->andWhere('c.status=1')
            ->setParameters(new ArrayCollection([
                new Parameter('category',$category_id),
                new Parameter('price', $price),
            ]));
        foreach($filters as $filter_name=>$filter_key){
            $query->andWhere('o.option_name = :fname and o.option_value=:fkey')
                ->setParameter('fname',$filter_name)
                ->setParameter('fkey',$filter_key);
        }
        return $query->orderBy('c.price', 'DESC')
            ->getQuery()
            ->getResult();
    }

}