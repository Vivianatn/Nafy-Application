<?php

namespace App\Controller;

use App\Repository\KitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class KitController extends AbstractController
{
    #[Route('/api/kits', name: 'api_kits_list', methods: ['GET'])]
    public function list(KitRepository $kitRepository): JsonResponse
    {
        $kits = array_map(
            static fn ($kit) => [
                'id' => $kit->getId(),
                'nom' => $kit->getNom(),
                'quantiteMax' => $kit->getQuantiteMax(),
            ],
            $kitRepository->findAllOrderedByNom(),
        );

        return $this->json($kits);
    }
}
