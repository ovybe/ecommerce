<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\ResultSetMapping;
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
            SELECT seller as filter_name, count(seller) as count_filter, "seller" as filter_column
                FROM product p
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                union
                select g.gpu_name as filter_name, g.gpu_count as filter_count, g.gpu_column as filter_column
                from (
                select interface as gpu_name, count(interface) as gpu_count, "interface" as gpu_column
                from gpu as g
                inner join product as p
                on g.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by gpu_name
                union
                select clock as gpu_name, count(clock) as gpu_count, "clock" as gpu_column
                from gpu g
                inner join product as p
                on g.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by gpu_name
                union
                select memory as gpu_name, count(memory) as gpu_count, "memory" as gpu_column
                from gpu g
                inner join product as p
                on g.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by gpu_name
                union
                select size as gpu_name, count(size) as gpu_count, "size" as gpu_column
                from gpu g
                inner join product as p
                on g.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by gpu_name
                union
                select series as gpu_name, count(series) as gpu_count, "series" as gpu_column
                from gpu g
                inner join product as p
                on g.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by gpu_name
                ) as g
                union
                select c.cpu_name as filter_name, c.cpu_count as filter_count, c.cpu_column as filter_column
                from (
                select socket as cpu_name, count(socket) as cpu_count, "socket" as cpu_column
                from cpu c
                inner join product as p
                on c.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cpu_name
                union
                select series as cpu_name, count(series) as cpu_count, "series" as cpu_column
                from cpu c
                inner join product as p
                on c.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cpu_name
                union
                select core as cpu_name, count(core) as cpu_count, "core" as cpu_column
                from cpu c
                inner join product as p
                on c.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cpu_name
                union
                select frequency as cpu_name, count(frequency) as cpu_count, "frequency" as cpu_column
                from cpu c
                inner join product as p
                on c.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cpu_name
                ) as c
                union
                select p.psu_name as filter_name, p.psu_count as filter_count, p.psu_column as filter_column
                from(
                select power as psu_name, count(power) as psu_count, "power" as psu_column
                from psu
                inner join product as p
                on psu.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by psu_name
                union
                select pfc as psu_name, count(pfc) as psu_count, "pfc" as psu_column
                from psu
                inner join product as p
                on psu.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by psu_name
                union
                select efficiency as psu_name, count(efficiency) as psu_count, "efficiency" as psu_column
                from psu
                inner join product as p
                on psu.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by psu_name
                union
                select certification as psu_name, count(certification) as psu_count, "certification" as psu_column
                from psu
                inner join product as p
                on psu.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by psu_name
                ) as p
                union
                select s.ssd_name as filter_name, s.ssd_count as filter_count, s.ssd_column as filter_column
                from(
                select series as ssd_name, count(series) as ssd_count, "series" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                union
                select interface as ssd_name, count(interface) as ssd_count, "interface" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                union
                select capacity as ssd_name, count(capacity) as ssd_count, "capacity" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                union
                select maxreading as ssd_name, count(maxreading) as ssd_count, "maxreading" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                union
                select buffer as ssd_name, count(buffer) as ssd_count, "buffer" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                union
                select drivetype as ssd_name, count(drivetype) as ssd_count, "drivetype" as ssd_column
                from ssd s
                inner join product as p
                on s.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by ssd_name
                ) as s
                union
                select mb.motherboard_name as filter_name, mb.motherboard_count as filter_count, mb.motherboard_column as filter_column
                from(
                select format as motherboard_name, count(format) as motherboard_count, "format" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select cpusocket as motherboard_name, count(cpusocket) as motherboard_count, "cpusocket" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select chipset as motherboard_name, count(chipset) as motherboard_count, "chipset" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select modelchipset as motherboard_name, count(modelchipset) as motherboard_count, "modelchipset" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select interface as motherboard_name, count(interface) as motherboard_count, "interface" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select memory as motherboard_name, count(memory) as motherboard_count, "memory" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                union
                select tech as motherboard_name, count(tech) as motherboard_count, "tech" as motherboard_column
                from motherboard mb
                inner join product as p
                on mb.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by motherboard_name
                ) as mb
                union
                select cl.cooler_name as filter_name, cl.cooler_count as filter_count, cl.cooler_column as filter_column
                from(
                select ctype as cooler_name, count(ctype) as cooler_count, "ctype" as cooler_column
                from cooler cl
                inner join product as p
                on cl.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cooler_name
                union
                select cooling as cooler_name, count(cooling) as cooler_count, "cooling" as cooler_column
                from cooler cl
                inner join product as p
                on cl.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cooler_name
                union
                select height as cooler_name, count(height) as cooler_count, "height" as cooler_column
                from cooler cl
                inner join product as p
                on cl.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cooler_name
                union
                select vents as cooler_name, count(vents) as cooler_count, "vents" as cooler_column
                from cooler cl
                inner join product as p
                on cl.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cooler_name
                union
                select size as cooler_name, count(size) as cooler_count, "size" as cooler_column
                from cooler cl
                inner join product as p
                on cl.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by cooler_name
                ) as cl
                union
                select m.memory_name as filter_name, m.memory_count as filter_count, m.memory_column as filter_column
                from(
                select memtype as memory_name, count(memtype) as memory_count, "memtype" as memory_column
                from memory m
                inner join product as p
                on m.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by memory_name
                union
                select capacity as memory_name, count(capacity) as memory_count, "capacity" as memory_column
                from memory m
                inner join product as p
                on m.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by memory_name
                union
                select frequency as memory_name, count(frequency) as memory_count, "frequency" as memory_column
                from memory m
                inner join product as p
                on m.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by memory_name
                union
                select latency as memory_name, count(latency) as memory_count, "latency" as memory_column
                from memory m
                inner join product as p
                on m.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by memory_name
                ) as m
                union
                select pc.pccase_name as filter_name, pc.pccase_count as filter_count, pc.pccase_column as filter_column
                from(
                select casetype as pccase_name, count(casetype) as pccase_count, "casetype" as pccase_column
                from pccase pc
                inner join product as p
                on pc.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by pccase_name
                union
                select height as pccase_name, count(height) as pccase_count, "height" as pccase_column
                from pccase pc
                inner join product as p
                on pc.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by pccase_name
                union
                select diameter as pccase_name, count(diameter) as pccase_count, "diameter" as pccase_column
                from pccase pc
                inner join product as p
                on pc.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by pccase_name
                union
                select width as pccase_name, count(width) as pccase_count, "width" as pccase_column
                from pccase pc
                inner join product as p
                on pc.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by pccase_name
                union
                select slots as pccase_name, count(slots) as pccase_count, "slots" as pccase_column
                from pccase pc
                inner join product as p
                on pc.id=p.id
                where p.name like ? or p.short_desc like ? or p.description like ? or p.sku like ?
                group by pccase_name
                ) as pc
        ';
        # number of "?" = 164
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        $any_value="%".$value."%";
        for($i=1;$i<165;$i++){
            $stmt->bindValue($i,$any_value);
        }
        return $stmt->executeQuery()->fetchAllAssociative();
    }
    /**
     * @return object[] The objects.
     */
    public function findAllByStatusAndQuantity(int $status,int $max): array
    {
        return $this->createQueryBuilder('c')
            ->select('c')
//            ->from($this->entityClass,'c')
//            ->leftJoin(Product::class,'p','WITH','c.i=p.id')
            ->where('c.status = :status_in')
            ->setParameter('status_in',$status)
            ->leftJoin(ProductInventory::class,'i', 'WITH',
                'c.id = i.product')
            ->groupBy('c.id')
            ->having('SUM(i.quantity)>0')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }
    /**
     * @return object[] The objects.
     */
    public function getFilters(): array
    {
        $sql = 'SELECT * FROM filter_view';
        $stmt= $this->getEntityManager()->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();

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