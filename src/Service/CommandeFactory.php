<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\DevisKit;
use App\Entity\Facture;
use App\Entity\FactureKit;
use App\Repository\KitRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CommandeFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KitRepository $kitRepository,
        private readonly VilleRepository $villeRepository,
        private readonly CommandePrixCalculator $prixCalculator,
        private readonly CommandeNumeroGenerator $numeroGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $prix
     */
    public function creerDevis(array $data, array $prix): Devis
    {
        $devis = new Devis();
        $this->hydraterCommande($devis, $data, $prix);

        foreach ($data['clients'] as $clientData) {
            if (!is_array($clientData)) {
                continue;
            }
            $client = $this->creerClient($clientData);
            $devis->addClient($client);
        }

        foreach ($this->lignesKitsValides($data) as [$kitId, $quantite]) {
            $ligne = new DevisKit();
            $ligne->setKit($this->kitRepository->find($kitId));
            $ligne->setQuantiteChoisie($quantite);
            $devis->addLigneKit($ligne);
        }

        $devis->setNumero($this->numeroGenerator->genererPourDevis($data));

        $this->entityManager->persist($devis);
        $this->entityManager->flush();

        return $devis;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $prix
     */
    public function creerFacture(array $data, array $prix, ?string $numeroPrefere = null): Facture
    {
        $facture = new Facture();
        $this->hydraterCommande($facture, $data, $prix);

        foreach ($data['clients'] as $clientData) {
            if (!is_array($clientData)) {
                continue;
            }
            $client = $this->creerClient($clientData);
            $facture->addClient($client);
        }

        foreach ($this->lignesKitsValides($data) as [$kitId, $quantite]) {
            $ligne = new FactureKit();
            $ligne->setKit($this->kitRepository->find($kitId));
            $ligne->setQuantiteChoisie($quantite);
            $facture->addLigneKit($ligne);
        }

        $facture->setNumero($this->numeroGenerator->genererPourFacture($data, $numeroPrefere));

        $this->entityManager->persist($facture);
        $this->entityManager->flush();

        return $facture;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
     */
    public function calculerPrixDepuisDonnees(array $data, bool $avecArrhes): array
    {
        $livraison = $data['livraison'] === true;
        $kilometres = 0;

        if ($livraison) {
            $ville = $this->villeRepository->find((int) ($data['villeId'] ?? 0));
            $kilometres = $ville?->getKilometres() ?? 0;
        }

        return $this->prixCalculator->calculer(
            $this->totalKits($data),
            $livraison,
            $kilometres,
            $data['vaisselleANettoyer'] === true,
            $avecArrhes,
            $avecArrhes,
        );
    }

    /**
     * @param Devis|Facture $commande
     * @param array<string, mixed> $data
     * @param array<string, string> $prix
     */
    private function hydraterCommande(object $commande, array $data, array $prix): void
    {
        $commande->setAdresseEvenement(trim((string) $data['adresseEvenement']));
        $commande->setDateReservation(
            !empty($data['dateReservation'])
                ? new \DateTimeImmutable((string) $data['dateReservation'])
                : null,
        );
        $commande->setChandeliers($data['chandeliers'] === true);
        $commande->setQuantiteChandeliers(
            $data['chandeliers'] === true ? (int) ($data['quantiteChandeliers'] ?? 0) : null,
        );

        $optionFlutes = $data['flutesVerresOption'] ?? null;
        $commande->setFlutesVerresOption(
            in_array($optionFlutes, ['flutes', 'verres', 'les2'], true) ? $optionFlutes : null,
        );
        $commande->setQuantiteFlutesVerres(
            $optionFlutes ? (int) ($data['quantiteFlutesVerres'] ?? 0) : null,
        );

        $commande->setLivraison($data['livraison'] === true);
        $commande->setVille(
            $data['livraison'] === true
                ? $this->villeRepository->find((int) ($data['villeId'] ?? 0))
                : null,
        );

        $commande->setVaisselleANettoyer($data['vaisselleANettoyer'] === true);
        $commande->setDateRentree(
            !empty($data['dateRentree'])
                ? new \DateTimeImmutable((string) $data['dateRentree'])
                : null,
        );

        $commande->setPrixKits($prix['prixKits']);
        $commande->setPrixLivraison($prix['prixLivraison']);
        $commande->setPrixLavage($prix['prixLavage']);
        $commande->setPrixFinal($prix['prixFinal']);

        if ($commande instanceof Devis) {
            $commande->setPrixCaution($prix['prixCaution']);
            $commande->setPrixArrhes($prix['prixArrhes']);
        } elseif ($commande instanceof Facture) {
            $commande->setPrixCaution('0.00');
        }

        $note = trim((string) ($data['noteCommande'] ?? ''));
        $commande->setNoteCommande($note !== '' ? $note : null);
        $commande->setConditionCasse($data['conditionCasse'] === true);
        $commande->setConditionCaution($data['conditionCaution'] === true);
        $commande->setConditionReservation($data['conditionReservation'] === true);
        $commande->setBonPourAccord($data['bonPourAccord'] === true);
    }

    /**
     * @param array<string, mixed> $clientData
     */
    private function creerClient(array $clientData): Client
    {
        $client = new Client();
        $client->setNom(trim((string) $clientData['nom']));
        $client->setPrenom(trim((string) $clientData['prenom']));

        $adresse = trim((string) ($clientData['adresse'] ?? ''));
        $client->setAdresse($adresse !== '' ? $adresse : null);

        $telephone = trim((string) ($clientData['telephone'] ?? ''));
        $client->setTelephone($telephone !== '' ? $telephone : null);

        return $client;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<array{0: int, 1: int}>
     */
    private function lignesKitsValides(array $data): array
    {
        $lignes = [];

        foreach ($data['kits'] ?? [] as $ligne) {
            if (!is_array($ligne)) {
                continue;
            }

            $kitId = (int) ($ligne['kitId'] ?? 0);
            $quantite = (int) ($ligne['quantite'] ?? 0);

            if ($kitId > 0 && $quantite > 0) {
                $lignes[] = [$kitId, $quantite];
            }
        }

        return $lignes;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function totalKits(array $data): int
    {
        $total = 0;

        foreach ($this->lignesKitsValides($data) as [, $quantite]) {
            $total += $quantite;
        }

        return $total;
    }
}
