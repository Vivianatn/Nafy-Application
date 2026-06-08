<?php

namespace App\Repository;

use App\Entity\JetonReinitialisationMotDePasse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JetonReinitialisationMotDePasse>
 */
class JetonReinitialisationMotDePasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JetonReinitialisationMotDePasse::class);
    }

    public function supprimerJetonsUtilisateur(string $typeUtilisateur, int $utilisateurId): void
    {
        $this->createQueryBuilder('j')
            ->delete()
            ->andWhere('j.typeUtilisateur = :type')
            ->andWhere('j.utilisateurId = :id')
            ->setParameter('type', $typeUtilisateur)
            ->setParameter('id', $utilisateurId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return list<JetonReinitialisationMotDePasse>
     */
    public function findAllNonExpires(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.utiliseLe IS NULL')
            ->andWhere('j.expireLe > :maintenant')
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
