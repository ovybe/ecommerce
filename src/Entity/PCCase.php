<?php

namespace App\Entity;

use App\Repository\PCCaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PCCaseRepository::class)]
class PCCase extends Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $casetype = null;

    #[ORM\Column]
    private ?float $height = null;

    #[ORM\Column]
    private ?float $diameter = null;

    #[ORM\Column]
    private ?float $width = null;

    #[ORM\Column]
    private ?int $slots = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCasetype(): ?string
    {
        return $this->casetype;
    }

    public function setCasetype(string $casetype): self
    {
        $this->casetype = $casetype;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getDiameter(): ?float
    {
        return $this->diameter;
    }

    public function setDiameter(float $diameter): self
    {
        $this->diameter = $diameter;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getSlots(): ?int
    {
        return $this->slots;
    }

    public function setSlots(int $slots): self
    {
        $this->slots = $slots;

        return $this;
    }
}
