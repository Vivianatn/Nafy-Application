<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\Table(name: 'facture')]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80, unique: true, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseEvenement = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateReservation = null;

    #[ORM\Column]
    private bool $chandeliers = false;

    #[ORM\Column(nullable: true)]
    private ?int $quantiteChandeliers = null;

    /** 'flutes' | 'verres' | 'les2' | null */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flutesVerresOption = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantiteFlutesVerres = null;

    #[ORM\Column]
    private bool $livraison = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Ville $ville = null;

    #[ORM\Column]
    private bool $vaisselleANettoyer = false;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateRentree = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixKits = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixLivraison = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixLavage = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixCaution = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixFinal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $noteCommande = null;

    #[ORM\Column]
    private bool $conditionCasse = false;

    #[ORM\Column]
    private bool $conditionCaution = false;

    #[ORM\Column]
    private bool $conditionReservation = false;

    #[ORM\Column]
    private bool $bonPourAccord = false;

    /** @var Collection<int, Client> */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'facture', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $clients;

    /** @var Collection<int, FactureKit> */
    #[ORM\OneToMany(targetEntity: FactureKit::class, mappedBy: 'facture', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignesKits;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->clients = new ArrayCollection();
        $this->lignesKits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAdresseEvenement(): ?string
    {
        return $this->adresseEvenement;
    }

    public function setAdresseEvenement(?string $adresseEvenement): static
    {
        $this->adresseEvenement = $adresseEvenement;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->dateReservation;
    }

    public function setDateReservation(?\DateTimeImmutable $dateReservation): static
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function isChandeliers(): bool
    {
        return $this->chandeliers;
    }

    public function setChandeliers(bool $chandeliers): static
    {
        $this->chandeliers = $chandeliers;

        return $this;
    }

    public function getQuantiteChandeliers(): ?int
    {
        return $this->quantiteChandeliers;
    }

    public function setQuantiteChandeliers(?int $quantiteChandeliers): static
    {
        $this->quantiteChandeliers = $quantiteChandeliers;

        return $this;
    }

    public function getFlutesVerresOption(): ?string
    {
        return $this->flutesVerresOption;
    }

    public function setFlutesVerresOption(?string $flutesVerresOption): static
    {
        $this->flutesVerresOption = $flutesVerresOption;

        return $this;
    }

    public function getQuantiteFlutesVerres(): ?int
    {
        return $this->quantiteFlutesVerres;
    }

    public function setQuantiteFlutesVerres(?int $quantiteFlutesVerres): static
    {
        $this->quantiteFlutesVerres = $quantiteFlutesVerres;

        return $this;
    }

    public function isLivraison(): bool
    {
        return $this->livraison;
    }

    public function setLivraison(bool $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function isVaisselleANettoyer(): bool
    {
        return $this->vaisselleANettoyer;
    }

    public function setVaisselleANettoyer(bool $vaisselleANettoyer): static
    {
        $this->vaisselleANettoyer = $vaisselleANettoyer;

        return $this;
    }

    public function getDateRentree(): ?\DateTimeImmutable
    {
        return $this->dateRentree;
    }

    public function setDateRentree(?\DateTimeImmutable $dateRentree): static
    {
        $this->dateRentree = $dateRentree;

        return $this;
    }

    public function getPrixKits(): ?string
    {
        return $this->prixKits;
    }

    public function setPrixKits(?string $prixKits): static
    {
        $this->prixKits = $prixKits;

        return $this;
    }

    public function getPrixLivraison(): ?string
    {
        return $this->prixLivraison;
    }

    public function setPrixLivraison(?string $prixLivraison): static
    {
        $this->prixLivraison = $prixLivraison;

        return $this;
    }

    public function getPrixLavage(): ?string
    {
        return $this->prixLavage;
    }

    public function setPrixLavage(?string $prixLavage): static
    {
        $this->prixLavage = $prixLavage;

        return $this;
    }

    public function getPrixCaution(): ?string
    {
        return $this->prixCaution;
    }

    public function setPrixCaution(?string $prixCaution): static
    {
        $this->prixCaution = $prixCaution;

        return $this;
    }

    public function getPrixFinal(): ?string
    {
        return $this->prixFinal;
    }

    public function setPrixFinal(?string $prixFinal): static
    {
        $this->prixFinal = $prixFinal;

        return $this;
    }

    public function getNoteCommande(): ?string
    {
        return $this->noteCommande;
    }

    public function setNoteCommande(?string $noteCommande): static
    {
        $this->noteCommande = $noteCommande;

        return $this;
    }

    public function isConditionCasse(): bool
    {
        return $this->conditionCasse;
    }

    public function setConditionCasse(bool $conditionCasse): static
    {
        $this->conditionCasse = $conditionCasse;

        return $this;
    }

    public function isConditionCaution(): bool
    {
        return $this->conditionCaution;
    }

    public function setConditionCaution(bool $conditionCaution): static
    {
        $this->conditionCaution = $conditionCaution;

        return $this;
    }

    public function isConditionReservation(): bool
    {
        return $this->conditionReservation;
    }

    public function setConditionReservation(bool $conditionReservation): static
    {
        $this->conditionReservation = $conditionReservation;

        return $this;
    }

    public function isBonPourAccord(): bool
    {
        return $this->bonPourAccord;
    }

    public function setBonPourAccord(bool $bonPourAccord): static
    {
        $this->bonPourAccord = $bonPourAccord;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setFacture($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            if ($client->getFacture() === $this) {
                $client->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactureKit>
     */
    public function getLignesKits(): Collection
    {
        return $this->lignesKits;
    }

    public function addLigneKit(FactureKit $ligneKit): static
    {
        if (!$this->lignesKits->contains($ligneKit)) {
            $this->lignesKits->add($ligneKit);
            $ligneKit->setFacture($this);
        }

        return $this;
    }

    public function removeLigneKit(FactureKit $ligneKit): static
    {
        if ($this->lignesKits->removeElement($ligneKit)) {
            if ($ligneKit->getFacture() === $this) {
                $ligneKit->setFacture(null);
            }
        }

        return $this;
    }
}
