<?php

namespace App\Service;

use App\Repository\DevisRepository;
use App\Repository\FactureRepository;

final class CommandeNumeroGenerator
{
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
    public function genererPourFacture(array $data): string
    {
        $now = new \DateTimeImmutable();
        $telephone = $this->extraireTelephoneClient($data);
        $sequence = $this->factureRepository->countForYear((int) $now->format('Y')) + 1;

        return $this->composerNumero($now, $telephone, $sequence);
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
