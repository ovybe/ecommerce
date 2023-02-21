<?php

namespace App\Entity;

use App\Repository\CoolerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoolerRepository::class)]
class Cooler extends Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $ctype = null;

    #[ORM\Column]
    private ?bool $cooling = null;

    #[ORM\Column]
    private ?float $height = null;

    #[ORM\Column]
    private ?int $vents = null;

    #[ORM\Column]
    private ?float $size = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCtype(): ?string
    {
        return $this->ctype;
    }

    public function setCtype(string $ctype): self
    {
        $this->ctype = $ctype;

        return $this;
    }

    public function isCooling(): ?bool
    {
        return $this->cooling;
    }

    public function setCooling(bool $cooling): self
    {
        $this->cooling = $cooling;

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

    public function getVents(): ?int
    {
        return $this->vents;
    }

    public function setVents(int $vents): self
    {
        $this->vents = $vents;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }
}
