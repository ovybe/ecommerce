<?php

namespace App\Entity;

use App\Repository\CpuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CpuRepository::class)]
class Cpu extends Product
{

    #[ORM\Column(nullable: true)]
    private ?int $socket = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $series = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $core = null;

    #[ORM\Column(nullable: true)]
    private ?float $frequency = null;


    public function getSocket(): ?int
    {
        return $this->socket;
    }

    public function setSocket(?int $socket): self
    {
        $this->socket = $socket;

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

    public function getCore(): ?string
    {
        return $this->core;
    }

    public function setCore(?string $core): self
    {
        $this->core = $core;

        return $this;
    }

    public function getFrequency(): ?float
    {
        return $this->frequency;
    }

    public function setFrequency(?float $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }
}
