<?php

namespace App\Entity;

use App\Repository\MemoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemoryRepository::class)]
class Memory extends Product
{


    #[ORM\Column(length: 10, nullable: true)]
    private ?string $memtype = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?int $frequency = null;

    #[ORM\Column(nullable: true)]
    private ?int $latency = null;

    public function getMemtype(): ?string
    {
        return $this->memtype;
    }

    public function setMemtype(?string $memtype): self
    {
        $this->memtype = $memtype;

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

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getLatency(): ?int
    {
        return $this->latency;
    }

    public function setLatency(?int $latency): self
    {
        $this->latency = $latency;

        return $this;
    }
}
