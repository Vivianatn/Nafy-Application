<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\Facture;
use App\Repository\DevisRepository;
use App\Repository\FactureRepository;
use App\Repository\KitRepository;

final class InventaireStockCalculator
{
    public function __construct(
        private readonly KitRepository $kitRepository,
        private readonly DevisRepository $devisRepository,
        private readonly FactureRepository $factureRepository,
    ) {
    }

    /**
     * @return list<array{
     *     id: int,
     *     nom: string,
     *     quantiteMax: int,
     *     quantiteReservee: int,
     *     quantiteDisponible: int
     * }>
     */
    public function calculerPourDate(\DateTimeImmutable $date): array
    {
        $date = $date->setTime(0, 0);

        $kits = $this->kitRepository->findAllOrderedByNom();
        $reserves = [];

        foreach ($kits as $kit) {
            $reserves[$kit->getId()] = 0;
        }

        foreach ($this->devisRepository->findAllWithLignesKits() as $devis) {
            $this->accumulerReserves($reserves, $date, $devis);
        }

        foreach ($this->factureRepository->findAllWithLignesKits() as $facture) {
            $this->accumulerReserves($reserves, $date, $facture);
        }

        $resultat = [];

        foreach ($kits as $kit) {
            $kitId = $kit->getId();
            $quantiteMax = $kit->getQuantiteMax();
            $quantiteReservee = $reserves[$kitId] ?? 0;

            $resultat[] = [
                'id' => $kitId,
                'nom' => $kit->getNom(),
                'quantiteMax' => $quantiteMax,
                'quantiteReservee' => $quantiteReservee,
                'quantiteDisponible' => max(0, $quantiteMax - $quantiteReservee),
            ];
        }

        return $resultat;
    }

    /**
     * @param array<int, int> $reserves
     */
    private function accumulerReserves(array &$reserves, \DateTimeImmutable $date, Devis|Facture $commande): void
    {
        if (!$this->estKitIndisponible($date, $commande)) {
            return;
        }

        foreach ($commande->getLignesKits() as $ligne) {
            $kit = $ligne->getKit();
            if ($kit === null) {
                continue;
            }

            $kitId = $kit->getId();
            $reserves[$kitId] = ($reserves[$kitId] ?? 0) + ($ligne->getQuantiteChoisie() ?? 0);
        }
    }

    private function estKitIndisponible(\DateTimeImmutable $date, Devis|Facture $commande): bool
    {
        $dateReservation = $commande->getDateReservation();
        if ($dateReservation === null) {
            return false;
        }

        $debut = $dateReservation->setTime(0, 0);

        if ($commande->isVaisselleANettoyer() && $commande->getDateRentree() !== null) {
            // Jour de lavage : indisponible jusqu'à la veille de la remise en stock.
            $fin = $commande->getDateRentree()->setTime(0, 0)->modify('+1 day');
        } elseif ($commande->getDateRentree() !== null) {
            $fin = $commande->getDateRentree()->setTime(0, 0);
        } else {
            $fin = $debut;
        }

        return $date >= $debut && $date <= $fin;
    }
}
