<?php

namespace App\Service;

use App\Entity\Devis;

final class CommandeSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialiserDevisPourFormulaire(Devis $devis): array
    {
        $clients = [];

        foreach ($devis->getClients() as $client) {
            $clients[] = [
                'nom' => $client->getNom() ?? '',
                'prenom' => $client->getPrenom() ?? '',
                'adresse' => $client->getAdresse() ?? '',
                'telephone' => $client->getTelephone() ?? '',
            ];
        }

        $kits = [];

        foreach ($devis->getLignesKits() as $ligne) {
            $kit = $ligne->getKit();

            if ($kit === null) {
                continue;
            }

            $kits[] = [
                'kitId' => $kit->getId(),
                'quantite' => $ligne->getQuantiteChoisie(),
            ];
        }

        return [
            'devisId' => $devis->getId(),
            'devisNumero' => $devis->getNumero(),
            'clients' => $clients,
            'adresseEvenement' => $devis->getAdresseEvenement() ?? '',
            'dateReservation' => $devis->getDateReservation()?->format('Y-m-d') ?? '',
            'kits' => $kits,
            'chandeliers' => $devis->isChandeliers(),
            'quantiteChandeliers' => $devis->getQuantiteChandeliers(),
            'flutesVerresOption' => $devis->getFlutesVerresOption(),
            'quantiteFlutesVerres' => $devis->getQuantiteFlutesVerres(),
            'livraison' => $devis->isLivraison(),
            'villeId' => $devis->getVille()?->getId(),
            'vaisselleANettoyer' => $devis->isVaisselleANettoyer(),
            'dateRentree' => $devis->getDateRentree()?->format('Y-m-d') ?? '',
            'noteCommande' => $devis->getNoteCommande() ?? '',
            'conditionCasse' => $devis->isConditionCasse(),
            'conditionCaution' => $devis->isConditionCaution(),
            'conditionReservation' => $devis->isConditionReservation(),
            'bonPourAccord' => $devis->isBonPourAccord(),
        ];
    }
}
