<?php

namespace App\Repository;

use App\Entity\Responsable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Responsable>
 */
class ResponsableRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Responsable::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Responsable) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByIdentifiant(string $identifiant): ?Responsable
    {
        $identifiant = trim($identifiant);

        if ($identifiant === '') {
            return null;
        }

        if (str_contains($identifiant, '@')) {
            return $this->findOneBy(['email' => $identifiant]);
        }

        $telephoneNormalise = self::normaliserTelephone($identifiant);
        if ($telephoneNormalise === '') {
            return null;
        }

        $candidats = $this->createQueryBuilder('r')
            ->andWhere('r.telephone IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($candidats as $responsable) {
            if (self::normaliserTelephone((string) $responsable->getTelephone()) === $telephoneNormalise) {
                return $responsable;
            }
        }

        return null;
    }

    private static function normaliserTelephone(string $telephone): string
    {
        $chiffres = preg_replace('/\D+/', '', $telephone);

        return $chiffres ?? '';
    }
}
