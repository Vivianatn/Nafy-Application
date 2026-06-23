<?php

namespace App\Controller;

use App\Repository\DevisRepository;
use App\Service\CommandeFactory;
use App\Service\CommandePdfGenerator;
use App\Service\CommandeSerializer;
use App\Service\CommandeValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevisController extends AbstractController
{
    #[Route('/api/devis', name: 'api_devis_list', methods: ['GET'])]
    public function list(DevisRepository $devisRepository): JsonResponse
    {
        $devis = array_map(
            static fn ($item) => [
                'id' => $item->getId(),
                'numero' => $item->getNumero(),
                'createdAt' => $item->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'dateReservation' => $item->getDateReservation()?->format('Y-m-d'),
                'heureRecuperationVaisselle' => $item->getHeureRecuperationVaisselle()?->format('H:i'),
                'adresseEvenement' => $item->getAdresseEvenement(),
                'dateRentree' => $item->getDateRentree()?->format('Y-m-d'),
                'prixFinal' => $item->getPrixFinal(),
                'noteCommande' => $item->getNoteCommande() ?? '',
            ],
            $devisRepository->findAllOrderedByCreatedAtDesc(),
        );

        return $this->json($devis);
    }

    #[Route('/api/devis/{id}', name: 'api_devis_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, DevisRepository $devisRepository, CommandeSerializer $serializer): JsonResponse
    {
        $devis = $devisRepository->findOneWithDetails($id);

        if ($devis === null) {
            return $this->json(['message' => 'Devis introuvable.'], 404);
        }

        return $this->json($serializer->serialiserDevisPourFormulaire($devis));
    }

    #[Route('/api/devis', name: 'api_devis_create', methods: ['POST'])]
    public function create(
        Request $request,
        CommandeValidator $validator,
        CommandeFactory $factory,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $erreurs = $validator->valider($data);

        if ($erreurs !== []) {
            return $this->json(['erreurs' => $erreurs], 422);
        }

        $prix = $factory->calculerPrixDepuisDonnees($data, true);
        $devis = $factory->creerDevis($data, $prix);

        return $this->json([
            'id' => $devis->getId(),
            'numero' => $devis->getNumero(),
            'createdAt' => $devis->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'dateReservation' => $devis->getDateReservation()?->format('Y-m-d'),
            'prixKits' => $devis->getPrixKits(),
            'prixLivraison' => $devis->getPrixLivraison(),
            'prixLavage' => $devis->getPrixLavage(),
            'prixCaution' => $devis->getPrixCaution(),
            'prixArrhes' => $devis->getPrixArrhes(),
            'prixFinal' => $devis->getPrixFinal(),
        ], 201);
    }

    #[Route('/api/devis/{id}/facture', name: 'api_devis_creer_facture', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function creerFacture(
        int $id,
        DevisRepository $devisRepository,
        CommandeSerializer $serializer,
        CommandeValidator $validator,
        CommandeFactory $factory,
    ): JsonResponse {
        $devis = $devisRepository->findOneWithDetails($id);

        if ($devis === null) {
            return $this->json(['message' => 'Devis introuvable.'], 404);
        }

        $data = $serializer->serialiserDevisPourFormulaire($devis);
        unset($data['devisId'], $data['devisNumero']);

        $erreurs = $validator->valider($data);

        if ($erreurs !== []) {
            return $this->json([
                'message' => 'Le devis ne contient pas toutes les informations nécessaires pour créer une facture.',
                'erreurs' => $erreurs,
            ], 422);
        }

        $prix = $factory->calculerPrixDepuisDonnees($data, false);
        $facture = $factory->creerFacture($data, $prix, $devis->getNumero());

        return $this->json([
            'id' => $facture->getId(),
            'numero' => $facture->getNumero(),
            'devisId' => $devis->getId(),
            'devisNumero' => $devis->getNumero(),
            'createdAt' => $facture->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'dateReservation' => $facture->getDateReservation()?->format('Y-m-d'),
            'prixKits' => $facture->getPrixKits(),
            'prixLivraison' => $facture->getPrixLivraison(),
            'prixLavage' => $facture->getPrixLavage(),
            'prixCaution' => $facture->getPrixCaution(),
            'prixFinal' => $facture->getPrixFinal(),
        ], 201);
    }

    #[Route('/api/devis/{id}/heure-recuperation', name: 'api_devis_heure_recuperation', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function mettreAJourHeureRecuperation(
        int $id,
        Request $request,
        DevisRepository $devisRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $devis = $devisRepository->find($id);

        if ($devis === null) {
            return $this->json(['message' => 'Devis introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Corps de requête JSON invalide.'], 400);
        }

        $heureBrute = trim((string) ($data['heure'] ?? ''));

        if ($heureBrute === '') {
            $devis->setHeureRecuperationVaisselle(null);
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $heureBrute)) {
            return $this->json(['message' => 'Heure invalide (format HH:MM attendu).'], 422);
        } else {
            $heure = \DateTimeImmutable::createFromFormat('H:i', $heureBrute);
            if ($heure === false) {
                return $this->json(['message' => 'Heure invalide.'], 422);
            }
            $devis->setHeureRecuperationVaisselle($heure);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $devis->getId(),
            'heureRecuperationVaisselle' => $devis->getHeureRecuperationVaisselle()?->format('H:i'),
        ]);
    }

    #[Route('/api/devis/{id}', name: 'api_devis_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, DevisRepository $devisRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $devis = $devisRepository->find($id);

        if ($devis === null) {
            return $this->json(['message' => 'Devis introuvable.'], 404);
        }

        $entityManager->remove($devis);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    #[Route('/api/devis/{id}/pdf', name: 'api_devis_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdf(int $id, DevisRepository $devisRepository, CommandePdfGenerator $pdfGenerator): Response
    {
        $devis = $devisRepository->find($id);

        if ($devis === null) {
            return $this->json(['message' => 'Devis introuvable.'], 404);
        }

        return $pdfGenerator->genererDevis($devis);
    }
}
