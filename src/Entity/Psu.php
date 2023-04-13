<?php

namespace App\Entity;

use App\Repository\PsuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PsuRepository::class)]
class Psu extends Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(groups: ['need_validation'])]
    private ?int $power = null;

    #[ORM\ManyToMany(targetEntity: Vent::class, inversedBy: 'psus')]
    private Collection $vents;

    #[ORM\Column(nullable: true)]
    private ?bool $pfc = null;

    #[ORM\Column(nullable: true)]
    private ?int $efficiency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certification = null;

    public function __construct()
    {
        parent::__construct();
        $this->vents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(int $power): self
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return Collection<int, Vent>
     */
    public function getVents(): Collection
    {
        return $this->vents;
    }

    public function addVent(Vent $vent): self
    {
        if (!$this->vents->contains($vent)) {
            $this->vents->add($vent);
        }

        return $this;
    }

    public function removeVent(Vent $vent): self
    {
        $this->vents->removeElement($vent);

        return $this;
    }

    public function isPfc(): ?bool
    {
        return $this->pfc;
    }

    public function setPfc(?bool $pfc): self
    {
        $this->pfc = $pfc;

        return $this;
    }

    public function getEfficiency(): ?int
    {
        return $this->efficiency;
    }

    public function setEfficiency(?int $efficiency): self
    {
        $this->efficiency = $efficiency;

        return $this;
    }

    public function getCertification(): ?string
    {
        return $this->certification;
    }

    public function setCertification(?string $certification): self
    {
        $this->certification = $certification;

        return $this;
    }
}
