<?php

namespace App\Controller;

use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    #[Route('/api/villes', name: 'api_villes_list', methods: ['GET'])]
    public function list(VilleRepository $villeRepository): JsonResponse
    {
        $villes = array_map(
            static fn ($ville) => [
                'id' => $ville->getId(),
                'nom' => $ville->getNom(),
                'departement' => $ville->getDepartement(),
                'kilometres' => $ville->getKilometres(),
            ],
            $villeRepository->findAllOrderedByNom(),
        );

        return $this->json($villes);
    }
}
