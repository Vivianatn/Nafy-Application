<?php

namespace App\Controller;

use App\Entity\Responsable;
use App\Repository\ResponsableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_RESPONSABLE')]
class ResponsableController extends AbstractController
{
    #[Route('/api/responsables', name: 'api_responsable_create', methods: ['POST'])]
    public function create(
        Request $request,
        ResponsableRepository $responsableRepository,
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

        if (!isset($erreurs['email']) && $responsableRepository->findOneBy(['email' => $email]) !== null) {
            $erreurs['email'] = 'Un responsable avec cet email existe déjà.';
        }

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $responsable = new Responsable();
        $responsable->setNom($nom);
        $responsable->setPrenom($prenom);
        $responsable->setEmail($email);
        $responsable->setTelephone($telephone !== '' ? $telephone : null);
        $responsable->setRoles(['ROLE_RESPONSABLE']);
        $responsable->setPassword($passwordHasher->hashPassword($responsable, $motDePasse));

        $entityManager->persist($responsable);
        $entityManager->flush();

        return $this->json([
            'id' => $responsable->getId(),
            'nom' => $responsable->getNom(),
            'prenom' => $responsable->getPrenom(),
            'email' => $responsable->getEmail(),
            'telephone' => $responsable->getTelephone(),
        ], 201);
    }
}
