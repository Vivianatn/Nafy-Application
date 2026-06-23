<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CommandePdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        private readonly OrganisateurInfo $organisateurInfo,
    ) {
    }

    public function genererDevis(Devis $devis): Response
    {
        $donnees = $this->construireDonnees($devis, true);

        return $this->generer('devis', $this->libelleNumero($devis->getNumero(), $devis->getId()), $donnees);
    }

    public function genererFacture(Facture $facture): Response
    {
        $donnees = $this->construireDonnees($facture, false);

        return $this->generer('facture', $this->libelleNumero($facture->getNumero(), $facture->getId()), $donnees);
    }

    /**
     * @param Devis|Facture $commande
     */
    private function construireDonnees(object $commande, bool $avecArrhes): array
    {
        $lignesKits = [];
        foreach ($commande->getLignesKits() as $ligne) {
            $kit = $ligne->getKit();
            if ($kit === null) {
                continue;
            }
            $lignesKits[] = [
                'nom' => $kit->getNom(),
                'quantite' => $ligne->getQuantiteChoisie(),
            ];
        }

        $clients = [];
        foreach ($commande->getClients() as $client) {
            $clients[] = [
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'adresse' => $client->getAdresse(),
                'telephone' => $client->getTelephone(),
            ];
        }

        $ville = $commande->getVille();

        return [
            'avecArrhes' => $avecArrhes,
            'numero' => $this->libelleNumero($commande->getNumero(), $commande->getId()),
            'createdAt' => $commande->getCreatedAt(),
            'adresseEvenement' => $commande->getAdresseEvenement(),
            'dateReservation' => $commande->getDateReservation(),
            'chandeliers' => $commande->isChandeliers(),
            'quantiteChandeliers' => $commande->getQuantiteChandeliers(),
            'flutesVerresOption' => $this->libelleFlutesVerres($commande->getFlutesVerresOption()),
            'quantiteFlutesVerres' => $commande->getQuantiteFlutesVerres(),
            'livraison' => $commande->isLivraison(),
            'villeNom' => $ville?->getNom(),
            'villeDepartement' => $ville?->getDepartement(),
            'villeKilometres' => $ville?->getKilometres(),
            'vaisselleANettoyer' => $commande->isVaisselleANettoyer(),
            'dateRentree' => $commande->getDateRentree(),
            'prixKits' => $this->formaterPrix($commande->getPrixKits()),
            'prixLivraison' => $this->formaterPrix($commande->getPrixLivraison()),
            'prixLavage' => $this->formaterPrix($commande->getPrixLavage()),
            'prixCaution' => $this->formaterPrix($commande->getPrixCaution()),
            'prixArrhes' => $avecArrhes ? $this->formaterPrix($commande->getPrixArrhes()) : null,
            'prixFinal' => $this->formaterPrix($commande->getPrixFinal()),
            'noteCommande' => $commande->getNoteCommande(),
            'conditionCasse' => $commande->isConditionCasse(),
            'conditionCaution' => $commande->isConditionCaution(),
            'conditionReservation' => $commande->isConditionReservation(),
            'bonPourAccord' => $commande->isBonPourAccord(),
            'clients' => $clients,
            'lignesKits' => $lignesKits,
        ];
    }

    private function generer(string $type, string $numero, array $donnees): Response
    {
        $html = $this->twig->render('pdf/commande.html.twig', array_merge($donnees, [
            'type' => $type,
            'titreDocument' => $type === 'devis' ? 'Devis' : 'Facture',
        ], $this->organisateurInfo->pourPdf()));

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf('%s-%s.pdf', $type, preg_replace('/[^\w\-]+/', '_', $numero));

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"; filename*=UTF-8\'\'%s', $filename, rawurlencode($filename)),
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function libelleNumero(?string $numero, ?int $id): string
    {
        if ($numero !== null && $numero !== '') {
            return $numero;
        }

        return (string) ($id ?? '');
    }

    private function libelleFlutesVerres(?string $option): ?string
    {
        return match ($option) {
            'flutes' => 'Louer uniquement des flûtes',
            'verres' => 'Louer uniquement des verres',
            'les2' => 'Louer les 2',
            default => null,
        };
    }

    private function formaterPrix(?string $montant): string
    {
        if ($montant === null || $montant === '') {
            return '0,00';
        }

        return number_format((float) $montant, 2, ',', ' ');
    }
}
