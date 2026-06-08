<?php

namespace App\Service;

final class CommandePrixCalculator
{
    /**
     * @return array{
     *     prixKits: string,
     *     prixLivraison: string,
     *     prixLavage: string,
     *     prixCaution: string,
     *     prixFinal: string,
     *     prixArrhes: string
     * }
     */
    public function calculer(int $totalKits, bool $livraison, int $kilometres, bool $vaisselleANettoyer, bool $avecArrhes = false, bool $avecCaution = true): array
    {
        $prixKits = $this->multiplier($totalKits, 4);
        $prixLivraison = $livraison ? $this->multiplier($kilometres, 3) : '0.00';
        $prixLavage = $vaisselleANettoyer ? $this->multiplier($totalKits, 2) : '0.00';
        $prixCaution = $avecCaution && $vaisselleANettoyer ? $this->multiplier($totalKits, 5) : '0.00';

        $sousTotalPrestations = $this->additionner($prixKits, $prixLivraison, $prixLavage);
        $prixArrhes = $avecArrhes ? $this->pourcentage($sousTotalPrestations, 30) : '0.00';
        $prixFinal = $avecCaution
            ? $this->additionner($sousTotalPrestations, $prixCaution, $prixArrhes)
            : $this->additionner($sousTotalPrestations, $prixArrhes);

        return [
            'prixKits' => $prixKits,
            'prixLivraison' => $prixLivraison,
            'prixLavage' => $prixLavage,
            'prixCaution' => $prixCaution,
            'prixFinal' => $prixFinal,
            'prixArrhes' => $prixArrhes,
        ];
    }

    private function multiplier(int $quantite, int $tarif): string
    {
        return $this->formater((string) ($quantite * $tarif));
    }

    private function additionner(string ...$montants): string
    {
        $total = '0';

        foreach ($montants as $montant) {
            $total = bcadd($total, $montant, 2);
        }

        return $this->formater($total);
    }

    private function pourcentage(string $montant, int $pourcent): string
    {
        return $this->formater(bcmul($montant, bcdiv((string) $pourcent, '100', 4), 2));
    }

    private function formater(string $montant): string
    {
        return bcadd($montant, '0', 2);
    }
}
