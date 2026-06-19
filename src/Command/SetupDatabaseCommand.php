<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup-database',
    description: 'Applique les migrations et peuple les données de référence (kits, villes).',
)]
class SetupDatabaseCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Alias conservé pour les scripts (sans effet)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->connection->executeQuery('SELECT 1');
            $nomBase = $this->connection->getDatabase();
            $io->success(sprintf('Connexion à la base de données « %s » : OK', $nomBase));
        } catch (\Throwable $exception) {
            $io->error('Impossible de se connecter à la base de données.');
            $io->text($exception->getMessage());
            $io->note(
                'Depuis le conteneur symfony-web, DATABASE_URL doit utiliser l\'hôte « db » (ex. mysql://root:PASSWORD@db:3306/symfony).'
            );

            return Command::FAILURE;
        }

        $application = $this->getApplication();
        if ($application === null) {
            $io->error('Application Symfony indisponible.');

            return Command::FAILURE;
        }

        $migrate = $application->find('doctrine:migrations:migrate');
        $code = $migrate->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), $output);

        if ($code !== Command::SUCCESS) {
            return $code;
        }

        foreach (['app:seed-kits', 'app:seed-villes'] as $commandName) {
            $code = $application->find($commandName)->run(new ArrayInput([
                'command' => $commandName,
            ]), $output);
            if ($code !== Command::SUCCESS) {
                return $code;
            }
        }

        $io->section('État des données');
        $this->afficherCompteur($io, 'kit');
        $this->afficherCompteur($io, 'ville');
        $this->afficherCompteur($io, 'secretaire');
        $this->afficherCompteur($io, 'responsable');
        $this->afficherCompteur($io, 'devis');
        $this->afficherCompteur($io, 'facture');

        $io->success('Base de données prête.');

        return Command::SUCCESS;
    }

    private function afficherCompteur(SymfonyStyle $io, string $table): void
    {
        try {
            $count = (int) $this->connection->fetchOne(sprintf('SELECT COUNT(*) FROM %s', $table));
            $io->writeln(sprintf('  %s : %d', $table, $count));
        } catch (\Throwable) {
            $io->writeln(sprintf('  %s : (table absente)', $table));
        }
    }
}
