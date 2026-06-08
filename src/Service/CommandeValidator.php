<?php

namespace App\Service;

use App\Entity\Kit;
use App\Repository\KitRepository;
use App\Repository\VilleRepository;

final class CommandeValidator
{
    public function __construct(
        private readonly KitRepository $kitRepository,
        private readonly VilleRepository $villeRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    public function valider(array $data, bool $exigerArrhes = false): array
    {
        $erreurs = [];

        $clients = $data['clients'] ?? null;
        if (!is_array($clients) || $clients === []) {
            $erreurs['clients'] = 'Au moins un client est obligatoire.';
        } else {
            $client1 = $clients[0] ?? null;

            if (!is_array($client1)) {
                $erreurs['clients'] = 'Le client est invalide.';
            } else {
                if (trim((string) ($client1['nom'] ?? '')) === '') {
                    $erreurs['client1Nom'] = 'Le nom du client est obligatoire.';
                }
                if (trim((string) ($client1['prenom'] ?? '')) === '') {
                    $erreurs['client1Prenom'] = 'Le prénom du client est obligatoire.';
                }
                if (trim((string) ($client1['telephone'] ?? '')) === '') {
                    $erreurs['client1Telephone'] = 'Le numéro de téléphone (référence) est obligatoire.';
                }
            }

            if (isset($clients[1])) {
                $client2 = $clients[1];
                if (!is_array($client2)) {
                    $erreurs['clients'] = 'Le second client est invalide.';
                } else {
                    if (trim((string) ($client2['nom'] ?? '')) === '') {
                        $erreurs['client2Nom'] = 'Le nom du second client est obligatoire.';
                    }
                    if (trim((string) ($client2['prenom'] ?? '')) === '') {
                        $erreurs['client2Prenom'] = 'Le prénom du second client est obligatoire.';
                    }
                }
            }
        }

        if (trim((string) ($data['adresseEvenement'] ?? '')) === '') {
            $erreurs['adresseEvenement'] = "L'adresse de l'événement est obligatoire.";
        }

        if (trim((string) ($data['dateReservation'] ?? '')) === '') {
            $erreurs['dateReservation'] = 'La date de réservation est obligatoire.';
        }

        $kits = $data['kits'] ?? null;
        if (!is_array($kits) || $kits === []) {
            $erreurs['kits'] = 'Sélectionnez au moins un kit avec une quantité.';
        } else {
            $totalKits = 0;
            $kitsParId = [];

            foreach ($this->kitRepository->findAll() as $kit) {
                $kitsParId[$kit->getId()] = $kit;
            }

            foreach ($kits as $index => $ligne) {
                if (!is_array($ligne)) {
                    $erreurs['kits'] = 'Les lignes de kits sont invalides.';
                    break;
                }

                $kitId = (int) ($ligne['kitId'] ?? 0);
                $quantite = (int) ($ligne['quantite'] ?? 0);

                if ($kitId <= 0 || !isset($kitsParId[$kitId])) {
                    $erreurs['kits'] = 'Un kit sélectionné est invalide.';
                    break;
                }

                if ($quantite <= 0) {
                    continue;
                }

                /** @var Kit $kit */
                $kit = $kitsParId[$kitId];
                if ($quantite > $kit->getQuantiteMax()) {
                    $erreurs['kits'] = sprintf(
                        'La quantité pour « %s » dépasse le maximum (%d).',
                        $kit->getNom(),
                        $kit->getQuantiteMax(),
                    );
                    break;
                }

                $totalKits += $quantite;
            }

            if (!isset($erreurs['kits']) && $totalKits <= 0) {
                $erreurs['kits'] = 'Sélectionnez au moins un kit avec une quantité.';
            }
        }

        $livraison = $data['livraison'] ?? null;
        if ($livraison !== true && $livraison !== false) {
            $erreurs['livraison'] = 'Indiquez si une livraison est nécessaire.';
        } elseif ($livraison === true) {
            $villeId = (int) ($data['villeId'] ?? 0);
            if ($villeId <= 0 || $this->villeRepository->find($villeId) === null) {
                $erreurs['villeId'] = 'La ville de livraison est obligatoire.';
            }
        }

        $vaisselleANettoyer = $data['vaisselleANettoyer'] ?? null;
        if ($vaisselleANettoyer !== true && $vaisselleANettoyer !== false) {
            $erreurs['vaisselleANettoyer'] = 'Indiquez si la vaisselle doit être nettoyée.';
        }

        if (trim((string) ($data['dateRentree'] ?? '')) === '') {
            $erreurs['dateRentree'] = 'La date de rentrée est obligatoire.';
        }

        if (empty($data['conditionCasse'])) {
            $erreurs['conditionCasse'] = 'Cette condition doit être acceptée.';
        }
        if (empty($data['conditionCaution'])) {
            $erreurs['conditionCaution'] = 'Cette condition doit être acceptée.';
        }
        if (empty($data['conditionReservation'])) {
            $erreurs['conditionReservation'] = 'Cette condition doit être acceptée.';
        }
        if (empty($data['bonPourAccord'])) {
            $erreurs['bonPourAccord'] = 'Le bon pour accord est obligatoire.';
        }

        return $erreurs;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function totalKits(array $data): int
    {
        $total = 0;

        foreach ($data['kits'] ?? [] as $ligne) {
            if (!is_array($ligne)) {
                continue;
            }
            $quantite = (int) ($ligne['quantite'] ?? 0);
            if ($quantite > 0) {
                $total += $quantite;
            }
        }

        return $total;
    }
}
