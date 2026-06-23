<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/api/evenements', name: 'api_evenements_list', methods: ['GET'])]
    public function list(EvenementRepository $evenementRepository): JsonResponse
    {
        $evenements = array_map(
            static fn (Evenement $item) => self::serialiser($item),
            $evenementRepository->findAllOrderedByDate(),
        );

        return $this->json($evenements);
    }

    #[Route('/api/evenements/{id}', name: 'api_evenements_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, EvenementRepository $evenementRepository): JsonResponse
    {
        $evenement = $evenementRepository->find($id);

        if ($evenement === null) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        return $this->json(self::serialiser($evenement));
    }

    #[Route('/api/evenements', name: 'api_evenements_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $erreurs = self::valider($data);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $evenement = self::hydrater(new Evenement(), $data);
        $entityManager->persist($evenement);
        $entityManager->flush();

        return $this->json(self::serialiser($evenement), 201);
    }

    #[Route('/api/evenements/{id}', name: 'api_evenements_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, EvenementRepository $evenementRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $evenement = $evenementRepository->find($id);

        if ($evenement === null) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $erreurs = self::valider($data);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        self::hydrater($evenement, $data);
        $entityManager->flush();

        return $this->json(self::serialiser($evenement));
    }

    #[Route('/api/evenements/{id}/heure-recuperation', name: 'api_evenements_heure_recuperation', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function mettreAJourHeureRecuperation(
        int $id,
        Request $request,
        EvenementRepository $evenementRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $evenement = $evenementRepository->find($id);

        if ($evenement === null) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $heureBrute = trim((string) ($data['heure'] ?? ''));

        if ($heureBrute === '') {
            $evenement->setHeureRecuperationVaisselle(null);
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $heureBrute)) {
            return $this->json(['message' => 'Heure invalide (format HH:MM attendu).'], 422);
        } else {
            $heure = \DateTimeImmutable::createFromFormat('H:i', $heureBrute);
            if ($heure === false) {
                return $this->json(['message' => 'Heure invalide.'], 422);
            }
            $evenement->setHeureRecuperationVaisselle($heure);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $evenement->getId(),
            'heureRecuperationVaisselle' => $evenement->getHeureRecuperationVaisselle()?->format('H:i'),
        ]);
    }

    #[Route('/api/evenements/{id}', name: 'api_evenements_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, EvenementRepository $evenementRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $evenement = $evenementRepository->find($id);

        if ($evenement === null) {
            return $this->json(['message' => 'Événement introuvable.'], 404);
        }

        $entityManager->remove($evenement);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private static function serialiser(Evenement $evenement): array
    {
        return [
            'id' => $evenement->getId(),
            'type' => 'evenement',
            'titre' => $evenement->getTitre(),
            'adresseEvenement' => $evenement->getAdresseEvenement(),
            'dateReservation' => $evenement->getDateReservation()?->format('Y-m-d'),
            'heureRecuperationVaisselle' => $evenement->getHeureRecuperationVaisselle()?->format('H:i'),
            'dateRentree' => $evenement->getDateRentree()?->format('Y-m-d'),
            'note' => $evenement->getNote(),
            'createdAt' => $evenement->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    private static function valider(array $data): array
    {
        $erreurs = [];

        $dateReservation = trim((string) ($data['dateReservation'] ?? ''));
        if ($dateReservation === '') {
            $erreurs['dateReservation'] = 'La date de réservation est obligatoire.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateReservation)) {
            $erreurs['dateReservation'] = 'Format de date invalide.';
        }

        $dateRentree = trim((string) ($data['dateRentree'] ?? ''));
        if ($dateRentree !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRentree)) {
            $erreurs['dateRentree'] = 'Format de date invalide.';
        }

        return $erreurs;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function hydrater(Evenement $evenement, array $data): Evenement
    {
        $titre = trim((string) ($data['titre'] ?? ''));
        $adresse = trim((string) ($data['adresseEvenement'] ?? ''));
        $note = trim((string) ($data['note'] ?? ''));
        $dateRentree = trim((string) ($data['dateRentree'] ?? ''));
        $heureBrute = trim((string) ($data['heureRecuperationVaisselle'] ?? ''));

        $evenement->setTitre($titre !== '' ? $titre : null);
        $evenement->setAdresseEvenement($adresse !== '' ? $adresse : null);
        $evenement->setNote($note !== '' ? $note : null);
        $evenement->setDateReservation(new \DateTimeImmutable((string) $data['dateReservation']));

        if ($dateRentree !== '') {
            $evenement->setDateRentree(new \DateTimeImmutable($dateRentree));
        } else {
            $evenement->setDateRentree(null);
        }

        if ($heureBrute === '') {
            $evenement->setHeureRecuperationVaisselle(null);
        } elseif (preg_match('/^\d{2}:\d{2}$/', $heureBrute)) {
            $heure = \DateTimeImmutable::createFromFormat('H:i', $heureBrute);
            if ($heure !== false) {
                $evenement->setHeureRecuperationVaisselle($heure);
            }
        }

        return $evenement;
    }
}
