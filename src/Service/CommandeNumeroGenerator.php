<?php

namespace App\Service;

use App\Repository\DevisRepository;
use App\Repository\FactureRepository;

final class CommandeNumeroGenerator
{
    private const MAX_TENTATIVES_NUMERO = 999;

    public function __construct(
        private readonly DevisRepository $devisRepository,
        private readonly FactureRepository $factureRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function genererPourDevis(array $data): string
    {
        $now = new \DateTimeImmutable();
        $telephone = $this->extraireTelephoneClient($data);
        $sequence = $this->devisRepository->countForYear((int) $now->format('Y')) + 1;

        return $this->composerNumero($now, $telephone, $sequence);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function genererPourFacture(array $data, ?string $numeroPrefere = null): string
    {
        if ($numeroPrefere !== null && $numeroPrefere !== '') {
            return $this->trouverNumeroFactureDisponible($numeroPrefere, $data);
        }

        $now = new \DateTimeImmutable();
        $telephone = $this->extraireTelephoneClient($data);
        $sequence = $this->factureRepository->countForYear((int) $now->format('Y')) + 1;

        return $this->composerNumero($now, $telephone, $sequence);
    }

    private function trouverNumeroFactureDisponible(string $souhaite, array $data): string
    {
        if (!$this->factureRepository->existsByNumero($souhaite)) {
            return $souhaite;
        }

        if (preg_match('/^(.*-)(\d+)$/', $souhaite, $matches)) {
            $prefixe = $matches[1];
            $sequence = (int) $matches[2];

            for ($n = $sequence + 1; $n <= $sequence + self::MAX_TENTATIVES_NUMERO; ++$n) {
                $candidat = $prefixe.$n;
                if (!$this->factureRepository->existsByNumero($candidat)) {
                    return $candidat;
                }
            }
        }

        for ($n = 2; $n <= self::MAX_TENTATIVES_NUMERO; ++$n) {
            $candidat = $souhaite.'-'.$n;
            if (!$this->factureRepository->existsByNumero($candidat)) {
                return $candidat;
            }
        }

        return $this->genererPourFacture($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extraireTelephoneClient(array $data): string
    {
        $clients = $data['clients'] ?? [];
        $premierClient = is_array($clients[0] ?? null) ? $clients[0] : [];

        return $this->normaliserTelephone((string) ($premierClient['telephone'] ?? ''));
    }

    private function composerNumero(\DateTimeImmutable $date, string $telephone, int $sequence): string
    {
        return sprintf(
            '%s%s%s-%d',
            $date->format('Y'),
            $date->format('m'),
            $telephone,
            $sequence,
        );
    }

    private function normaliserTelephone(string $telephone): string
    {
        $chiffres = preg_replace('/\D+/', '', $telephone);

        return $chiffres !== '' && $chiffres !== null ? $chiffres : '0000000000';
    }
}
