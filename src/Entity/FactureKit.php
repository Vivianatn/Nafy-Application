<?php

namespace App\Entity;

use App\Repository\FactureKitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureKitRepository::class)]
#[ORM\Table(name: 'facture_kit')]
class FactureKit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignesKits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facture $facture = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kit $kit = null;

    #[ORM\Column]
    private ?int $quantiteChoisie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getKit(): ?Kit
    {
        return $this->kit;
    }

    public function setKit(?Kit $kit): static
    {
        $this->kit = $kit;

        return $this;
    }

    public function getQuantiteChoisie(): ?int
    {
        return $this->quantiteChoisie;
    }

    public function setQuantiteChoisie(int $quantiteChoisie): static
    {
        $this->quantiteChoisie = $quantiteChoisie;

        return $this;
    }
}
