<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:vider-devis-factures',
    description: 'Supprime tous les devis et factures (clients et lignes kits associés).',
)]
class ViderDevisFacturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Confirmer la suppression sans invite interactive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbDevis = (int) $this->entityManager->createQuery('SELECT COUNT(d.id) FROM App\Entity\Devis d')->getSingleScalarResult();
        $nbFactures = (int) $this->entityManager->createQuery('SELECT COUNT(f.id) FROM App\Entity\Facture f')->getSingleScalarResult();

        if ($nbDevis === 0 && $nbFactures === 0) {
            $io->success('Aucun devis ni facture à supprimer.');

            return Command::SUCCESS;
        }

        $io->warning(sprintf(
            'Cette action supprimera %d devis et %d factures, ainsi que leurs clients et lignes kits.',
            $nbDevis,
            $nbFactures,
        ));

        if (!$input->getOption('force') && !$io->confirm('Continuer ?', false)) {
            $io->note('Annulé.');

            return Command::SUCCESS;
        }

        // Ordre obligatoire à cause des clés étrangères en base.
        foreach (['App\Entity\DevisKit', 'App\Entity\FactureKit', 'App\Entity\Client', 'App\Entity\Devis', 'App\Entity\Facture'] as $entity) {
            $this->entityManager->createQuery(sprintf('DELETE FROM %s e', $entity))->execute();
        }

        $io->success('Devis, factures et données liées supprimés.');

        return Command::SUCCESS;
    }
}
