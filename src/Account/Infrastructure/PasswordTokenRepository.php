<?php

declare(strict_types=1);

namespace App\Account\Infrastructure;

use App\Account\Domain\PasswordToken;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordToken>
 *
 * @method PasswordToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordToken[]    findAll()
 * @method PasswordToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @codeCoverageIgnore Simply repository
 *
 * @infection-ignore-all
 */
class PasswordTokenRepository extends ServiceEntityRepository implements PasswordTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordToken::class);
    }

    public function getByToken(string $token, \DateTimeImmutable $now): ?PasswordToken
    {
        $qb = $this->createQueryBuilder('pt');

        /** @phpstan-ignore-next-line */
        return $qb
            ->where($qb->expr()->eq('pt.token', ':token'))
            ->andWhere($qb->expr()->isNull('pt.activatedAt'))
            ->andWhere($qb->expr()->gt('pt.expiredAt', ':now'))
            ->setParameter('token', $token)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PasswordToken $passwordToken): void
    {
        $this->getEntityManager()->persist($passwordToken);
        $this->getEntityManager()->flush();
    }
}
