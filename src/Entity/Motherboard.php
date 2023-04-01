<?php

namespace App\Entity;

use App\Repository\MotherboardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MotherboardRepository::class)]
class Motherboard extends Product
{

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $format = null;

    #[ORM\Column(length: 5)]
//    #[Assert\Valid]
    #[Assert\NotNull(groups: ['need_validation'])]
    private ?string $cpusocket = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $chipset = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $modelchipset = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $interface = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $memory = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tech = null;

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getCpusocket(): ?string
    {
        return $this->cpusocket;
    }

    public function setCpusocket(string $cpusocket): self
    {
        $this->cpusocket = $cpusocket;

        return $this;
    }

    public function getChipset(): ?string
    {
        return $this->chipset;
    }

    public function setChipset(?string $chipset): self
    {
        $this->chipset = $chipset;

        return $this;
    }

    public function getModelchipset(): ?string
    {
        return $this->modelchipset;
    }

    public function setModelchipset(?string $modelchipset): self
    {
        $this->modelchipset = $modelchipset;

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

    public function getMemory(): ?string
    {
        return $this->memory;
    }

    public function setMemory(?string $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getTech(): ?string
    {
        return $this->tech;
    }

    public function setTech(?string $tech): self
    {
        $this->tech = $tech;

        return $this;
    }
}
