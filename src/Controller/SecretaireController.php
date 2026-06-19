<?php

namespace App\Controller;

use App\Entity\Secretaire;
use App\Repository\SecretaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_RESPONSABLE')]
class SecretaireController extends AbstractController
{
    #[Route('/api/secretaires', name: 'api_secretaire_list', methods: ['GET'])]
    public function list(SecretaireRepository $secretaireRepository): JsonResponse
    {
        $secretaires = $secretaireRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        return $this->json(array_map(
            fn (Secretaire $secretaire) => $this->serialiserSecretaire($secretaire),
            $secretaires,
        ));
    }

    #[Route('/api/secretaires/{id}', name: 'api_secretaire_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, SecretaireRepository $secretaireRepository): JsonResponse
    {
        $secretaire = $secretaireRepository->find($id);

        if ($secretaire === null) {
            return $this->json(['message' => 'Secrétaire introuvable.'], 404);
        }

        return $this->json($this->serialiserSecretaire($secretaire));
    }

    #[Route('/api/secretaires', name: 'api_secretaire_create', methods: ['POST'])]
    public function create(
        Request $request,
        SecretaireRepository $secretaireRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $telephone = trim((string) ($data['telephone'] ?? ''));
        $motDePasse = (string) ($data['motDePasse'] ?? '');
        $confirmation = (string) ($data['confirmationMotDePasse'] ?? '');

        $erreurs = [];

        if ($nom === '') {
            $erreurs['nom'] = 'Le nom est obligatoire.';
        }
        if ($prenom === '') {
            $erreurs['prenom'] = 'Le prénom est obligatoire.';
        }
        if ($email === '') {
            $erreurs['email'] = "L'adresse email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = "L'adresse email n'est pas valide.";
        }
        if (mb_strlen($motDePasse) < 8) {
            $erreurs['motDePasse'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($motDePasse !== $confirmation) {
            $erreurs['confirmationMotDePasse'] = 'Les mots de passe ne correspondent pas.';
        }

        if (!isset($erreurs['email']) && $secretaireRepository->findOneBy(['email' => $email]) !== null) {
            $erreurs['email'] = 'Une secrétaire avec cet email existe déjà.';
        }

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $secretaire = new Secretaire();
        $secretaire->setNom($nom);
        $secretaire->setPrenom($prenom);
        $secretaire->setEmail($email);
        $secretaire->setTelephone($telephone !== '' ? $telephone : null);
        $secretaire->setRoles(['ROLE_SECRETAIRE']);
        $secretaire->setPassword($passwordHasher->hashPassword($secretaire, $motDePasse));

        $entityManager->persist($secretaire);
        $entityManager->flush();

        return $this->json($this->serialiserSecretaire($secretaire), 201);
    }

    /**
     * @return array{id: int|null, nom: string|null, prenom: string|null, email: string|null, telephone: string|null}
     */
    private function serialiserSecretaire(Secretaire $secretaire): array
    {
        return [
            'id' => $secretaire->getId(),
            'nom' => $secretaire->getNom(),
            'prenom' => $secretaire->getPrenom(),
            'email' => $secretaire->getEmail(),
            'telephone' => $secretaire->getTelephone(),
        ];
    }
}
