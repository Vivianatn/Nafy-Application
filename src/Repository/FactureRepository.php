<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    /**
     * @return list<Facture>
     */
    public function findAllOrderedByCreatedAtDesc(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countForYear(int $year): int
    {
        $debut = new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year));
        $fin = new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year + 1));

        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.createdAt >= :debut')
            ->andWhere('f.createdAt < :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function existsByNumero(string $numero): bool
    {
        return $this->count(['numero' => $numero]) > 0;
    }

    /**
     * @return list<Facture>
     */
    public function findAllWithLignesKitsAffectantDate(\DateTimeImmutable $date): array
    {
        $dateVeille = $date->modify('-1 day');

        return $this->createQueryBuilder('f')
            ->leftJoin('f.lignesKits', 'fk')->addSelect('fk')
            ->leftJoin('fk.kit', 'k')->addSelect('k')
            ->where('f.dateReservation IS NOT NULL')
            ->andWhere('f.dateReservation <= :date')
            ->andWhere('(
                (f.vaisselleANettoyer = true AND f.dateRentree IS NOT NULL AND f.dateRentree >= :dateVeille)
                OR (f.vaisselleANettoyer = false AND f.dateRentree IS NOT NULL AND f.dateRentree >= :date)
                OR (f.dateRentree IS NULL AND f.dateReservation >= :date)
            )')
            ->setParameter('date', $date)
            ->setParameter('dateVeille', $dateVeille)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Facture>
     */
    public function findAllWithLignesKits(): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.lignesKits', 'fk')->addSelect('fk')
            ->leftJoin('fk.kit', 'k')->addSelect('k')
            ->getQuery()
            ->getResult();
    }
}
