<?php

namespace App\Repository;

use App\Entity\Devis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Devis>
 */
class DevisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Devis::class);
    }

    /**
     * @return list<Devis>
     */
    public function findAllOrderedByCreatedAtDesc(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countForYear(int $year): int
    {
        $debut = new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year));
        $fin = new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year + 1));

        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.createdAt >= :debut')
            ->andWhere('d.createdAt < :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneWithDetails(int $id): ?Devis
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.clients', 'c')->addSelect('c')
            ->leftJoin('d.lignesKits', 'dk')->addSelect('dk')
            ->leftJoin('dk.kit', 'k')->addSelect('k')
            ->leftJoin('d.ville', 'v')->addSelect('v')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<Devis>
     */
    public function findAllWithLignesKits(): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.lignesKits', 'dk')->addSelect('dk')
            ->leftJoin('dk.kit', 'k')->addSelect('k')
            ->getQuery()
            ->getResult();
    }
}
