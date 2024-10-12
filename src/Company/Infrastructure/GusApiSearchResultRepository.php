<?php

declare(strict_types=1);

namespace App\Company\Infrastructure;

use App\Company\Domain\GusApiSearchResult;
use App\Company\Domain\GusApiSearchResultRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GusApiSearchResult>
 *
 * @method GusApiSearchResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method GusApiSearchResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method GusApiSearchResult[]    findAll()
 * @method GusApiSearchResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @codeCoverageIgnore Simply repository
 *
 * @infection-ignore-all
 */
class GusApiSearchResultRepository extends ServiceEntityRepository implements GusApiSearchResultRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GusApiSearchResult::class);
    }

    public function save(GusApiSearchResult $gusApiSearchResult): void
    {
        $this->getEntityManager()->persist($gusApiSearchResult);
        $this->getEntityManager()->flush();
    }

    /**
     * @return GusApiSearchResult[]
     */
    public function findAllCreatedAfter(\DateTimeImmutable $date): array
    {
        $qb = $this->createQueryBuilder('g');

        /** @var GusApiSearchResult[] */
        return $qb
            ->where($qb->expr()->gte('g.createdAt', ':date'))
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function remove(GusApiSearchResult $gusApiSearchResult): void
    {
        $this->getEntityManager()->remove($gusApiSearchResult);
        $this->getEntityManager()->flush();
    }
}
