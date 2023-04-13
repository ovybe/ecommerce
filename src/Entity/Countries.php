<?php

namespace App\Entity;

use App\Repository\CountriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountriesRepository::class)]
class Countries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $iso3 = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $numeric_code = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $iso2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phonecode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $capital = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currency_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currency_symbol = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tld = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $native = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subregion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $timezones = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $translations = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $emoji = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $emojiU = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $flag = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wikiDataId = null;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: States::class)]
    private Collection $states;

    public function __construct()
    {
        $this->states = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIso3(): ?string
    {
        return $this->iso3;
    }

    public function setIso3(?string $iso3): self
    {
        $this->iso3 = $iso3;

        return $this;
    }

    public function getNumericCode(): ?string
    {
        return $this->numeric_code;
    }

    public function setNumericCode(?string $numeric_code): self
    {
        $this->numeric_code = $numeric_code;

        return $this;
    }

    public function getIso2(): ?string
    {
        return $this->iso2;
    }

    public function setIso2(?string $iso2): self
    {
        $this->iso2 = $iso2;

        return $this;
    }

    public function getPhonecode(): ?string
    {
        return $this->phonecode;
    }

    public function setPhonecode(?string $phonecode): self
    {
        $this->phonecode = $phonecode;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currency_name;
    }

    public function setCurrencyName(?string $currency_name): self
    {
        $this->currency_name = $currency_name;

        return $this;
    }

    public function getCurrencySymbol(): ?string
    {
        return $this->currency_symbol;
    }

    public function setCurrencySymbol(?string $currency_symbol): self
    {
        $this->currency_symbol = $currency_symbol;

        return $this;
    }

    public function getTld(): ?string
    {
        return $this->tld;
    }

    public function setTld(?string $tld): self
    {
        $this->tld = $tld;

        return $this;
    }

    public function getNative(): ?string
    {
        return $this->native;
    }

    public function setNative(?string $native): self
    {
        $this->native = $native;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getSubregion(): ?string
    {
        return $this->subregion;
    }

    public function setSubregion(?string $subregion): self
    {
        $this->subregion = $subregion;

        return $this;
    }

    public function getTimezones(): ?string
    {
        return $this->timezones;
    }

    public function setTimezones(?string $timezones): self
    {
        $this->timezones = $timezones;

        return $this;
    }

    public function getTranslations(): ?string
    {
        return $this->translations;
    }

    public function setTranslations(?string $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(?string $emoji): self
    {
        $this->emoji = $emoji;

        return $this;
    }

    public function getEmojiU(): ?string
    {
        return $this->emojiU;
    }

    public function setEmojiU(?string $emojiU): self
    {
        $this->emojiU = $emojiU;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getFlag(): ?int
    {
        return $this->flag;
    }

    public function setFlag(int $flag): self
    {
        $this->flag = $flag;

        return $this;
    }

    public function getWikiDataId(): ?string
    {
        return $this->wikiDataId;
    }

    public function setWikiDataId(?string $wikiDataId): self
    {
        $this->wikiDataId = $wikiDataId;

        return $this;
    }

    /**
     * @return Collection<int, States>
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    public function addState(States $state): self
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setCountry($this);
        }

        return $this;
    }

    public function removeState(States $state): self
    {
        if ($this->states->removeElement($state)) {
            // set the owning side to null (unless already changed)
            if ($state->getCountry() === $this) {
                $state->setCountry(null);
            }
        }

        return $this;
    }
}
