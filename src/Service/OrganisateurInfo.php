<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

final class OrganisateurInfo
{
    public function __construct(
        private readonly KernelInterface $kernel,
        #[Autowire('%organisateur.nom_entreprise%')]
        private readonly string $nomEntreprise,
        #[Autowire('%organisateur.nom_marque%')]
        private readonly string $nomMarque,
        #[Autowire('%organisateur.siret%')]
        private readonly string $siret,
        #[Autowire('%organisateur.logo_fichier%')]
        private readonly string $logoFichier,
    ) {
    }

    /**
     * @return array{nomEntreprise: string, nomMarque: string, siret: string, logoUrl: string, logoBase64: string|null}
     */
    public function pourApi(): array
    {
        return [
            'nomEntreprise' => $this->nomEntreprise,
            'nomMarque' => $this->nomMarque,
            'siret' => $this->siret,
            'logoUrl' => '/images/'.$this->logoFichier,
        ];
    }

    /**
     * @return array{nomEntreprise: string, nomMarque: string, siret: string, logoBase64: string|null}
     */
    public function pourPdf(): array
    {
        return [
            'nomEntreprise' => $this->nomEntreprise,
            'nomMarque' => $this->nomMarque,
            'siret' => $this->siret,
            'logoBase64' => $this->logoEnBase64(),
        ];
    }

    public function getNomEntreprise(): string
    {
        return $this->nomEntreprise;
    }

    public function getNomMarque(): string
    {
        return $this->nomMarque;
    }

    public function getSiret(): string
    {
        return $this->siret;
    }

    private function logoEnBase64(): ?string
    {
        $chemin = $this->kernel->getProjectDir().'/public/images/'.$this->logoFichier;

        if (!is_readable($chemin)) {
            return null;
        }

        $contenu = file_get_contents($chemin);
        if ($contenu === false) {
            return null;
        }

        $extension = strtolower(pathinfo($chemin, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contenu);
    }
}
