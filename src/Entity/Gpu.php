<?php

namespace App\Entity;

use App\Repository\GpuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GpuRepository::class)]
class Gpu extends Product
{

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(groups: ['need_validation'])]
    private ?string $interface = null;

    #[ORM\Column(nullable: true)]
    private ?int $clock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $memory = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(groups: ['need_validation'])]
    private ?int $size = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $releasedate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $series = null;


    public function getInterface(): ?string
    {
        return $this->interface;
    }

    public function setInterface(string $interface): self
    {
        $this->interface = $interface;

        return $this;
    }

    public function getClock(): ?int
    {
        return $this->clock;
    }

    public function setClock(?int $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    public function getMemory(): ?string
    {
        return $this->memory;
    }

    public function setMemory(?string $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getReleasedate(): ?\DateTimeInterface
    {
        return $this->releasedate;
    }

    public function setReleasedate(?\DateTimeInterface $releasedate): self
    {
        $this->releasedate = $releasedate;

        return $this;
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
}
