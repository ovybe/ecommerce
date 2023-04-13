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

    /**
     * @return object[] The objects.
     * @throws \Doctrine\DBAL\Exception
     */
    public function getNByType($number=5): array
    {
        $sql = 'select t.* FROM(
                    SELECT 
                        p.id,
                        p.uid,
                        p.name,
                        p.description,
                        p.sku,
                        p.thumbnail,
                        p.type,
                        p.seller,
                        p.price,
                        p.created_at,
                        g.interface gpuinterface,
                        g.clock,
                        g.memory gpumemory,
                        g.size gpusize,
                        g.releasedate,
                        g.series gpuseries,
                        c.socket,
                        c.series,
                        c.core,
                        c.frequency cpufrequency,
                        m.memtype,
                        m.capacity memcapacity,
                        m.frequency memfrequency,
                        m.latency,
                        mb.format,
                        mb.cpusocket,
                        mb.chipset,
                        mb.modelchipset,
                        mb.interface mbinterface,
                        mb.memory mbmemory,
                        mb.tech,
                        pc.casetype,
                        pc.height,
                        pc.diameter,
                        pc.width,
                        pc.slots,
                        psu.power,
                        psu.pfc,
                        psu.efficiency,
                        psu.certification,
                        s.series ssdseries,
                        s.interface ssdinterface,
                        s.capacity ssdcapacity,
                        s.maxreading,
                        s.buffer,
                        s.drivetype,
                        co.ctype,
                        co.cooling,
                        co.height coolerheight,
                        co.vents,
                        co.size coolersize,
                        row_number() OVER (PARTITION BY type ORDER BY type) AS ordinal 
                    FROM 
                        product p
                    LEFT JOIN
                        gpu g
                    ON p.id = g.id
                    LEFT JOIN
                        cpu c
                    ON p.id = c.id
                    LEFT JOIN
                        memory m
                    ON p.id = m.id
                    LEFT JOIN
                        motherboard mb
                    ON p.id = mb.id
                    LEFT JOIN
                        pccase pc
                    ON p.id = pc.id
                    LEFT JOIN
                        psu
                    ON p.id = psu.id
                    LEFT JOIN
                        ssd s
                    ON p.id = s.id
                    LEFT JOIN
                        cooler co
                    ON p.id = co.id
                        ) t
                where t.ordinal < ?';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1,$number);
        return $stmt->executeQuery()->fetchAllAssociative();

    }
}