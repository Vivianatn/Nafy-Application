<?php

namespace App\Service;

use App\Entity\Responsable;
use App\Entity\Secretaire;
use App\Security\UtilisateurInterneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class MotDePasseOublieService
{
    public function __construct(
        private readonly UtilisateurInterneProvider $utilisateurInterneProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function reinitialiserMotDePasse(string $identifiant, string $motDePasse, string $confirmation): array
    {
        $erreurs = [];

        if ($identifiant === '') {
            $erreurs['identifiant'] = 'Indiquez votre email ou numéro de téléphone.';
        }

        if (mb_strlen($motDePasse) < 8) {
            $erreurs['motDePasse'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($motDePasse !== $confirmation) {
            $erreurs['confirmationMotDePasse'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($erreurs !== []) {
            return $erreurs;
        }

        $utilisateur = $this->utilisateurInterneProvider->trouverUtilisateur($identifiant);

        if ($utilisateur === null) {
            $erreurs['identifiant'] = 'Aucun compte ne correspond à cet identifiant.';

            return $erreurs;
        }

        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $motDePasse));
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return [];
    }
}
