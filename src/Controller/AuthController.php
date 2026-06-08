<?php

namespace App\Controller;

use App\Security\ConnexionReussieHandler;
use App\Security\UtilisateurInterneProvider;
use App\Service\MotDePasseOublieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends AbstractController
{
    private const DUREE_SESSION_PERSISTANTE = 2592000;

    #[Route('/api/auth/connexion', name: 'api_auth_connexion', methods: ['POST'])]
    public function connexion(
        Request $request,
        UtilisateurInterneProvider $utilisateurInterneProvider,
        UserPasswordHasherInterface $passwordHasher,
        ConnexionReussieHandler $connexionReussieHandler,
        TokenStorageInterface $tokenStorage,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $identifiant = trim((string) ($data['identifiant'] ?? ''));
        $motDePasse = (string) ($data['motDePasse'] ?? '');
        $resterConnecte = filter_var($data['resterConnecte'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($identifiant === '' || $motDePasse === '') {
            return $this->json([
                'message' => 'Identifiant et mot de passe sont obligatoires.',
            ], 422);
        }

        try {
            $utilisateur = $utilisateurInterneProvider->loadUserByIdentifier($identifiant);
        } catch (UserNotFoundException) {
            return $this->json([
                'message' => 'Identifiant ou mot de passe incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($utilisateur, $motDePasse)) {
            return $this->json([
                'message' => 'Identifiant ou mot de passe incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $token = new UsernamePasswordToken($utilisateur, 'api', $utilisateur->getRoles());
            $tokenStorage->setToken($token);
            $dureeCookie = $resterConnecte ? self::DUREE_SESSION_PERSISTANTE : 0;
            $request->getSession()->getMetadataBag()->stampNew($dureeCookie);
        } catch (\Throwable) {
            return $this->json([
                'message' => 'Impossible de créer la session de connexion.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'connecte' => true,
            'utilisateur' => $connexionReussieHandler->serialiserUtilisateur($utilisateur),
        ]);
    }

    #[Route('/api/auth/deconnexion', name: 'api_auth_deconnexion', methods: ['POST'])]
    public function deconnexion(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $tokenStorage->setToken(null);

        if ($request->hasSession()) {
            $request->getSession()->invalidate();
        }

        return $this->json(['connecte' => false]);
    }

    #[Route('/api/auth/session', name: 'api_auth_session', methods: ['GET'])]
    public function session(
        TokenStorageInterface $tokenStorage,
        ConnexionReussieHandler $connexionReussieHandler,
    ): JsonResponse {
        $utilisateur = $tokenStorage->getToken()?->getUser();

        if (!$utilisateur instanceof UserInterface) {
            return $this->json(['connecte' => false]);
        }

        return $this->json([
            'connecte' => true,
            'utilisateur' => $connexionReussieHandler->serialiserUtilisateur($utilisateur),
        ]);
    }

    #[Route('/api/auth/mot-de-passe-oublie', name: 'api_auth_mot_de_passe_oublie', methods: ['POST'])]
    public function motDePasseOublie(Request $request, MotDePasseOublieService $motDePasseOublieService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $identifiant = trim((string) ($data['identifiant'] ?? ''));

        if ($identifiant === '') {
            return $this->json([
                'erreurs' => ['identifiant' => 'Indiquez votre email ou numéro de téléphone.'],
            ], 422);
        }

        $motDePasseOublieService->demanderReinitialisation($identifiant);

        return $this->json([
            'message' => 'Si un compte correspond à cet identifiant, un email de réinitialisation a été envoyé.',
        ]);
    }

    #[Route('/api/auth/reinitialiser-mot-de-passe', name: 'api_auth_reinitialiser_mot_de_passe', methods: ['POST'])]
    public function reinitialiserMotDePasse(Request $request, MotDePasseOublieService $motDePasseOublieService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $jeton = trim((string) ($data['jeton'] ?? ''));
        $motDePasse = (string) ($data['motDePasse'] ?? '');
        $confirmation = (string) ($data['confirmationMotDePasse'] ?? '');

        if ($jeton === '') {
            return $this->json([
                'erreurs' => ['jeton' => 'Lien de réinitialisation invalide.'],
            ], 422);
        }

        $erreurs = $motDePasseOublieService->reinitialiserMotDePasse($jeton, $motDePasse, $confirmation);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        return $this->json([
            'message' => 'Votre mot de passe a bien été mis à jour. Vous pouvez vous connecter.',
        ]);
    }
}
