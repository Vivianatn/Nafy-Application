<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class ConnexionReussieHandler
{
    public function __construct(
        private readonly UtilisateurInterneProvider $utilisateurInterneProvider,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialiserUtilisateur(UserInterface $utilisateur): array
    {
        if (!$utilisateur instanceof \App\Entity\Secretaire && !$utilisateur instanceof \App\Entity\Responsable) {
            throw new \InvalidArgumentException('Utilisateur non pris en charge.');
        }

        return [
            'type' => $this->utilisateurInterneProvider->typeUtilisateur($utilisateur),
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'telephone' => $utilisateur->getTelephone(),
            'roles' => $utilisateur->getRoles(),
        ];
    }
}
