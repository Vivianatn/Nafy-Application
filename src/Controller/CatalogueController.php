<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{
    #[Route('/api/catalogue/pdf', name: 'api_catalogue_pdf', methods: ['GET'])]
    public function pdf(): BinaryFileResponse
    {
        $chemin = $this->getParameter('kernel.project_dir') . '/public/documents/catalogue-produits.pdf';

        if (!is_readable($chemin)) {
            throw $this->createNotFoundException('Catalogue introuvable.');
        }

        $reponse = new BinaryFileResponse($chemin);
        $reponse->headers->set('Content-Type', 'application/pdf');
        $reponse->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'catalogue-produits.pdf',
        );

        return $reponse;
    }
}
