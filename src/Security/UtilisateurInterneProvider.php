<?php

namespace App\Security;

use App\Entity\Responsable;
use App\Entity\Secretaire;
use App\Repository\ResponsableRepository;
use App\Repository\SecretaireRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<Secretaire|Responsable>
 */
final class UtilisateurInterneProvider implements UserProviderInterface
{
    public function __construct(
        private readonly SecretaireRepository $secretaireRepository,
        private readonly ResponsableRepository $responsableRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $utilisateur = $this->trouverUtilisateur($identifier);

        if ($utilisateur === null) {
            throw new UserNotFoundException(sprintf('Aucun compte trouvé pour « %s ».', $identifier));
        }

        return $utilisateur;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Secretaire && !$user instanceof Responsable) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return Secretaire::class === $class || Responsable::class === $class;
    }

    public function trouverUtilisateur(string $identifiant): Secretaire|Responsable|null
    {
        $identifiant = trim($identifiant);

        if ($identifiant === '') {
            return null;
        }

        $secretaire = $this->secretaireRepository->findByIdentifiant($identifiant);
        if ($secretaire !== null) {
            return $secretaire;
        }

        return $this->responsableRepository->findByIdentifiant($identifiant);
    }

    public function typeUtilisateur(UserInterface $user): string
    {
        return $user instanceof Responsable ? 'responsable' : 'secretaire';
    }
}
