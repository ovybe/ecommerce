<?php

namespace App\Entity;

use App\Repository\SsdRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SsdRepository::class)]
class Ssd extends Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $series = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $interface = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxreading = null;

    #[ORM\Column(nullable: true)]
    private ?int $buffer = null;

    #[ORM\Column(length: 50)]
    private ?string $drivetype = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeries(): ?string
    {
        return $this->series;
    }

    public function setSeries(?string $series): self
    {
        $this->series = $series;

        return $this;
    }

    public function getInterface(): ?string
    {
        return $this->interface;
    }

    public function setInterface(?string $interface): self
    {
        $this->interface = $interface;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getMaxreading(): ?int
    {
        return $this->maxreading;
    }

    public function setMaxreading(?int $maxreading): self
    {
        $this->maxreading = $maxreading;

        return $this;
    }

    public function getBuffer(): ?int
    {
        return $this->buffer;
    }

    public function setBuffer(?int $buffer): self
    {
        $this->buffer = $buffer;

        return $this;
    }

    public function getDrivetype(): ?string
    {
        return $this->drivetype;
    }

    public function setDrivetype(?string $drivetype): self
    {
        $this->drivetype = $drivetype;

        return $this;
    }
}
