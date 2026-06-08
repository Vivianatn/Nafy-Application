<?php

namespace App\Repository;

use App\Entity\Secretaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Secretaire>
 */
class SecretaireRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Secretaire::class);
    }

    /**
     * Met à jour (re-hache) le mot de passe au fil du temps si nécessaire.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Secretaire) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByIdentifiant(string $identifiant): ?Secretaire
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

        $candidats = $this->createQueryBuilder('s')
            ->andWhere('s.telephone IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($candidats as $secretaire) {
            if (self::normaliserTelephone((string) $secretaire->getTelephone()) === $telephoneNormalise) {
                return $secretaire;
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
