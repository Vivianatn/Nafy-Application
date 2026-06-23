<?php

namespace App\Command;

use App\Entity\Kit;
use App\Repository\KitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-kits',
    description: 'Insère les kits de vaisselle dans la table kit.',
)]
class SeedKitsCommand extends Command
{
    private const QUANTITE_MAX = 300;

    /** @var list<string> */
    private const NOMS = [
        'Marie Eve',
        'Esther',
        'Abigaël',
        'Rachel',
        'Myriam',
        'Ketura',
        'Déborah',
        'Sarah',
        'Rébecca',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KitRepository $kitRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existants = [];
        foreach ($this->kitRepository->findAll() as $kit) {
            $existants[$kit->getNom()] = true;
        }

        $ajoutes = 0;
        $ignores = 0;

        foreach (self::NOMS as $nom) {
            if (isset($existants[$nom])) {
                ++$ignores;
                continue;
            }
            $existants[$nom] = true;

            $kit = new Kit();
            $kit->setNom($nom);
            $kit->setQuantiteMax(self::QUANTITE_MAX);
            $this->entityManager->persist($kit);
            ++$ajoutes;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d kits ajoutés, %d ignorés (déjà présents).',
            $ajoutes,
            $ignores,
        ));

        return Command::SUCCESS;
    }
}
