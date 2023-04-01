<?php

namespace App\Entity;

use App\Repository\VentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VentRepository::class)]
class Vent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: Psu::class, mappedBy: 'vents')]
    private Collection $psus;

    #[ORM\Column(nullable: true)]
    private ?int $count = null;

    public function __construct()
    {
        $this->psus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Psu>
     */
    public function getPsus(): Collection
    {
        return $this->psus;
    }

    public function addPsu(Psu $psu): self
    {
        if (!$this->psus->contains($psu)) {
            $this->psus->add($psu);
            $psu->addVent($this);
        }

        return $this;
    }

    public function removePsu(Psu $psu): self
    {
        if ($this->psus->removeElement($psu)) {
            $psu->removeVent($this);
        }

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
