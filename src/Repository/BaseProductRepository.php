<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

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
            ->orWhere('c.description LIKE :desc')
            ->orWhere('c.SKU LIKE :sku')
            ->setParameters(new ArrayCollection([
                new Parameter('name', '%'.$value.'%'),
                new Parameter('desc', '%'.$value.'%'),
                new Parameter('sku', '%'.$value.'%')
            ]))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}