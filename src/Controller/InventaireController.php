<?php

namespace App\Controller;

use App\Service\InventaireStockCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InventaireController extends AbstractController
{
    #[Route('/api/inventaire', name: 'api_inventaire_stocks', methods: ['GET'])]
    public function stocks(Request $request, InventaireStockCalculator $calculator): JsonResponse
    {
        $dateStr = $request->query->getString('date');

        if ($dateStr === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $this->json(['message' => 'Paramètre date obligatoire (AAAA-MM-JJ).'], 400);
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
        if ($date === false) {
            return $this->json(['message' => 'Date invalide.'], 400);
        }

        return $this->json($calculator->calculerPourDate($date));
    }
}
