<?php

namespace App\Controller;

use App\Entity\Kit;
use App\Repository\DevisKitRepository;
use App\Repository\FactureKitRepository;
use App\Repository\KitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class KitController extends AbstractController
{
    #[Route('/api/kits', name: 'api_kits_list', methods: ['GET'])]
    public function list(KitRepository $kitRepository): JsonResponse
    {
        $kits = array_map(
            static fn ($kit) => self::serialiser($kit),
            $kitRepository->findAllOrderedByNom(),
        );

        return $this->json($kits);
    }

    #[Route('/api/kits/{id}', name: 'api_kits_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, KitRepository $kitRepository): JsonResponse
    {
        $kit = $kitRepository->find($id);

        if ($kit === null) {
            return $this->json(['message' => 'Vaisselle introuvable.'], 404);
        }

        return $this->json(self::serialiser($kit));
    }

    #[Route('/api/kits', name: 'api_kits_create', methods: ['POST'])]
    public function create(Request $request, KitRepository $kitRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $erreurs = self::valider($data, $kitRepository);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $kit = new Kit();
        $kit->setNom(trim((string) $data['nom']));
        $kit->setQuantiteMax((int) $data['quantiteMax']);
        $entityManager->persist($kit);
        $entityManager->flush();

        return $this->json(self::serialiser($kit), 201);
    }

    #[Route('/api/kits/{id}', name: 'api_kits_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        Request $request,
        KitRepository $kitRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $kit = $kitRepository->find($id);

        if ($kit === null) {
            return $this->json(['message' => 'Vaisselle introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $erreurs = self::valider($data, $kitRepository, $kit);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $kit->setNom(trim((string) $data['nom']));
        $kit->setQuantiteMax((int) $data['quantiteMax']);
        $entityManager->flush();

        return $this->json(self::serialiser($kit));
    }

    #[Route('/api/kits/{id}', name: 'api_kits_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        KitRepository $kitRepository,
        DevisKitRepository $devisKitRepository,
        FactureKitRepository $factureKitRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $kit = $kitRepository->find($id);

        if ($kit === null) {
            return $this->json(['message' => 'Vaisselle introuvable.'], 404);
        }

        if ($devisKitRepository->count(['kit' => $kit]) > 0 || $factureKitRepository->count(['kit' => $kit]) > 0) {
            return $this->json([
                'message' => 'Impossible de supprimer cette vaisselle : elle est utilisée dans des devis ou factures.',
            ], 409);
        }

        $entityManager->remove($kit);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array{id: int|null, nom: string|null, quantiteMax: int|null}
     */
    private static function serialiser(Kit $kit): array
    {
        return [
            'id' => $kit->getId(),
            'nom' => $kit->getNom(),
            'quantiteMax' => $kit->getQuantiteMax(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    private static function valider(array $data, KitRepository $kitRepository, ?Kit $kitExistant = null): array
    {
        $erreurs = [];

        $nom = trim((string) ($data['nom'] ?? ''));
        if ($nom === '') {
            $erreurs['nom'] = 'Le nom est obligatoire.';
        } elseif (strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom ne peut pas dépasser 100 caractères.';
        } else {
            $doublon = $kitRepository->findOneBy(['nom' => $nom]);
            if ($doublon !== null && ($kitExistant === null || $doublon->getId() !== $kitExistant->getId())) {
                $erreurs['nom'] = 'Une vaisselle porte déjà ce nom.';
            }
        }

        if (!isset($data['quantiteMax']) || !is_numeric($data['quantiteMax'])) {
            $erreurs['quantiteMax'] = 'La quantité maximale est obligatoire.';
        } elseif ((int) $data['quantiteMax'] < 1) {
            $erreurs['quantiteMax'] = 'La quantité maximale doit être au moins 1.';
        }

        return $erreurs;
    }
}
