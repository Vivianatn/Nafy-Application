<?php

namespace App\Service;

use App\Entity\JetonReinitialisationMotDePasse;
use App\Entity\Responsable;
use App\Entity\Secretaire;
use App\Repository\JetonReinitialisationMotDePasseRepository;
use App\Security\UtilisateurInterneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class MotDePasseOublieService
{
    public function __construct(
        private readonly UtilisateurInterneProvider $utilisateurInterneProvider,
        private readonly JetonReinitialisationMotDePasseRepository $jetonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $urlApplication,
    ) {
    }

    public function demanderReinitialisation(string $identifiant): void
    {
        $utilisateur = $this->utilisateurInterneProvider->trouverUtilisateur($identifiant);

        if ($utilisateur === null) {
            return;
        }

        $type = $utilisateur instanceof Responsable ? 'responsable' : 'secretaire';
        $this->jetonRepository->supprimerJetonsUtilisateur($type, (int) $utilisateur->getId());

        $jetonClair = bin2hex(random_bytes(32));
        $jeton = new JetonReinitialisationMotDePasse();
        $jeton->setTypeUtilisateur($type);
        $jeton->setUtilisateurId((int) $utilisateur->getId());
        $jeton->setTokenHash(hash('sha256', $jetonClair));
        $jeton->setExpireLe(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->persist($jeton);
        $this->entityManager->flush();

        $lien = rtrim($this->urlApplication, '/').'/reinitialiser-mot-de-passe?jeton='.$jetonClair;

        $this->envoyerEmailReinitialisation($utilisateur, $lien);
    }

    /**
     * @return list<string> Erreurs de validation
     */
    public function reinitialiserMotDePasse(string $jetonClair, string $motDePasse, string $confirmation): array
    {
        $erreurs = [];

        if (mb_strlen($motDePasse) < 8) {
            $erreurs['motDePasse'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($motDePasse !== $confirmation) {
            $erreurs['confirmationMotDePasse'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($erreurs !== []) {
            return $erreurs;
        }

        $jeton = $this->trouverJetonValide($jetonClair);
        if ($jeton === null) {
            $erreurs['jeton'] = 'Ce lien de réinitialisation est invalide ou expiré.';

            return $erreurs;
        }

        $utilisateur = $this->chargerUtilisateur($jeton);
        if ($utilisateur === null) {
            $erreurs['jeton'] = 'Ce lien de réinitialisation est invalide ou expiré.';

            return $erreurs;
        }

        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $motDePasse));
        $jeton->setUtiliseLe(new \DateTimeImmutable());

        $this->entityManager->persist($utilisateur);
        $this->entityManager->persist($jeton);
        $this->entityManager->flush();

        return [];
    }

    private function trouverJetonValide(string $jetonClair): ?JetonReinitialisationMotDePasse
    {
        $hash = hash('sha256', $jetonClair);

        foreach ($this->jetonRepository->findAllNonExpires() as $jeton) {
            if (hash_equals((string) $jeton->getTokenHash(), $hash)) {
                return $jeton;
            }
        }

        return null;
    }

    private function chargerUtilisateur(JetonReinitialisationMotDePasse $jeton): Secretaire|Responsable|null
    {
        $id = (int) $jeton->getUtilisateurId();

        return match ($jeton->getTypeUtilisateur()) {
            'responsable' => $this->entityManager->find(Responsable::class, $id),
            'secretaire' => $this->entityManager->find(Secretaire::class, $id),
            default => null,
        };
    }

    private function envoyerEmailReinitialisation(Secretaire|Responsable $utilisateur, string $lien): void
    {
        $email = (new Email())
            ->from('noreply@kamille-events.fr')
            ->to((string) $utilisateur->getEmail())
            ->subject('Réinitialisation de votre mot de passe — Kamille Events')
            ->text(
                "Bonjour {$utilisateur->getPrenom()},\n\n".
                "Pour choisir un nouveau mot de passe, cliquez sur le lien suivant (valable 1 heure) :\n".
                $lien."\n\n".
                "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n".
                'Kamille Events',
            );

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->warning('Envoi email réinitialisation impossible.', [
                'erreur' => $exception->getMessage(),
                'lien' => $lien,
                'destinataire' => $utilisateur->getEmail(),
            ]);
        }
    }
}
