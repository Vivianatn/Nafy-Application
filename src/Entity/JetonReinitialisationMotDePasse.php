<?php

namespace App\Entity;

use App\Repository\JetonReinitialisationMotDePasseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JetonReinitialisationMotDePasseRepository::class)]
#[ORM\Table(name: 'jeton_reinitialisation_mot_de_passe')]
class JetonReinitialisationMotDePasse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** secretaire | responsable */
    #[ORM\Column(length: 20)]
    private ?string $typeUtilisateur = null;

    #[ORM\Column]
    private ?int $utilisateurId = null;

    #[ORM\Column(length: 255)]
    private ?string $tokenHash = null;

    #[ORM\Column]
    private \DateTimeImmutable $expireLe;

    #[ORM\Column]
    private \DateTimeImmutable $creeLe;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $utiliseLe = null;

    public function __construct()
    {
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeUtilisateur(): ?string
    {
        return $this->typeUtilisateur;
    }

    public function setTypeUtilisateur(string $typeUtilisateur): static
    {
        $this->typeUtilisateur = $typeUtilisateur;

        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(int $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;

        return $this;
    }

    public function getTokenHash(): ?string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): static
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    public function getExpireLe(): \DateTimeImmutable
    {
        return $this->expireLe;
    }

    public function setExpireLe(\DateTimeImmutable $expireLe): static
    {
        $this->expireLe = $expireLe;

        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function getUtiliseLe(): ?\DateTimeImmutable
    {
        return $this->utiliseLe;
    }

    public function setUtiliseLe(?\DateTimeImmutable $utiliseLe): static
    {
        $this->utiliseLe = $utiliseLe;

        return $this;
    }

    public function estValide(): bool
    {
        return $this->utiliseLe === null && $this->expireLe > new \DateTimeImmutable();
    }
}
