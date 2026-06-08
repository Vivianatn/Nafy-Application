<?php

namespace App\Entity;

use App\Repository\DevisKitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisKitRepository::class)]
#[ORM\Table(name: 'devis_kit')]
class DevisKit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignesKits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devis $devis = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kit $kit = null;

    #[ORM\Column]
    private ?int $quantiteChoisie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;

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
