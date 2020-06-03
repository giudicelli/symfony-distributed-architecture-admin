<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use giudicelli\DistributedArchitectureAdminBundle\Controller\Dto\SearchDto;
use giudicelli\DistributedArchitectureBundle\Entity\ProcessStatus;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository as _ProcessStatusRepository;

/**
 * @method null|ProcessStatus find($id, $lockMode = null, $lockVersion = null)
 * @method null|ProcessStatus findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessStatus[]    findAll()
 * @method ProcessStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessStatusRepository extends _ProcessStatusRepository
{
    public function findBySearchRequest(SearchDto $search): ?array
    {
        return $this->buildQuery($search)
            ->setMaxResults($search->getLimit())
            ->setFirstResult($search->getOffset())
            ->orderBy('ps.'.$search->getSort(), $search->getDirection())
            ->getQuery()
            ->getResult()
        ;
    }

    public function countBySearchRequest(SearchDto $search): int
    {
        return $this->buildQuery($search)
            ->select('count(ps.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function buildQuery(SearchDto $search): QueryBuilder
    {
        $query = $this->createQueryBuilder('ps');

        // Query filters
        $this->buildQueryGroup($query, $search);

        // Status filters
        $this->buildQueryStatus($query, $search);

        return $query;
    }

    private function buildQueryGroup(QueryBuilder $query, SearchDto $search)
    {
        if (!$search->getGroup()) {
            return;
        }

        $query->andWhere('ps.group LIKE :likeQuery')
            ->setParameter('likeQuery', '%{'.$search->getGroup().'}%')
        ;
    }

    private function buildQueryStatus(QueryBuilder $query, SearchDto $search)
    {
        if (!$search->getStatus()) {
            return;
        }
        $query->andWhere('ps.status = :status')
            ->setParameter('status', $search->getStatus())
        ;
    }
}
