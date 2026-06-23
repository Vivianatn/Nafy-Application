<?php

namespace App\Controller;

use App\Repository\FactureRepository;
use App\Service\CommandeFactory;
use App\Service\CommandePdfGenerator;
use App\Service\CommandeValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    #[Route('/api/factures', name: 'api_facture_list', methods: ['GET'])]
    public function list(FactureRepository $factureRepository): JsonResponse
    {
        $factures = array_map(
            static fn ($item) => [
                'id' => $item->getId(),
                'numero' => $item->getNumero(),
                'createdAt' => $item->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'dateReservation' => $item->getDateReservation()?->format('Y-m-d'),
                'adresseEvenement' => $item->getAdresseEvenement(),
                'dateRentree' => $item->getDateRentree()?->format('Y-m-d'),
                'prixFinal' => $item->getPrixFinal(),
                'noteCommande' => $item->getNoteCommande() ?? '',
            ],
            $factureRepository->findAllOrderedByCreatedAtDesc(),
        );

        return $this->json($factures);
    }

    #[Route('/api/factures', name: 'api_facture_create', methods: ['POST'])]
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

        $prix = $factory->calculerPrixDepuisDonnees($data, false);
        $facture = $factory->creerFacture($data, $prix);

        return $this->json([
            'id' => $facture->getId(),
            'numero' => $facture->getNumero(),
            'createdAt' => $facture->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'dateReservation' => $facture->getDateReservation()?->format('Y-m-d'),
            'prixKits' => $facture->getPrixKits(),
            'prixLivraison' => $facture->getPrixLivraison(),
            'prixLavage' => $facture->getPrixLavage(),
            'prixCaution' => $facture->getPrixCaution(),
            'prixFinal' => $facture->getPrixFinal(),
        ], 201);
    }

    #[Route('/api/factures/{id}', name: 'api_facture_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, FactureRepository $factureRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $facture = $factureRepository->find($id);

        if ($facture === null) {
            return $this->json(['message' => 'Facture introuvable.'], 404);
        }

        $entityManager->remove($facture);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    #[Route('/api/factures/{id}/pdf', name: 'api_facture_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdf(int $id, FactureRepository $factureRepository, CommandePdfGenerator $pdfGenerator): Response
    {
        $facture = $factureRepository->find($id);

        if ($facture === null) {
            return $this->json(['message' => 'Facture introuvable.'], 404);
        }

        return $pdfGenerator->genererFacture($facture);
    }
}
