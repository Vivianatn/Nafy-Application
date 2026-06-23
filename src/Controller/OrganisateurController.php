<?php

namespace App\Controller;

use App\Service\OrganisateurInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrganisateurController extends AbstractController
{
    #[Route('/api/organisateur', name: 'api_organisateur', methods: ['GET'])]
    public function info(OrganisateurInfo $organisateurInfo): JsonResponse
    {
        return $this->json($organisateurInfo->pourApi());
    }
}
