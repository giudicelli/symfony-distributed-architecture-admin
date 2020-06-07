<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use giudicelli\DistributedArchitectureAdminBundle\Controller\Dto\SearchDto;
use giudicelli\DistributedArchitectureBundle\Repository\ProcessStatusRepository as _ProcessStatusRepository;

/**
 * @author Frédéric Giudicelli
 *
 * {@inheritdoc}
 */
class ProcessStatusRepository extends _ProcessStatusRepository
{
    /**
     * Return all statuses matching a search.
     *
     * @param SearchDto $search The search
     *
     * @return null|array<ProcessStatus> The list of matching statuses or null if there is none
     */
    public function findBySearchRequest(SearchDto $search): ?array
    {
        $query = $this->buildQuery($search);

        if ($search->getLimit()) {
            $query
                ->setMaxResults($search->getLimit())
                ->setFirstResult($search->getOffset())
            ;
        }
        if ($search->getSort()) {
            $query
                ->orderBy('ps.'.$search->getSort(), $search->getDirection())
            ;
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return the total number of statuses matching a search.
     *
     * @param SearchDto $search The search
     *
     * @return int The number of statuses
     */
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
        $this->buildQueryGroup($query, $search);
        $this->buildQueryStatus($query, $search);
        $this->buildQueryCommand($query, $search);
        $this->buildQueryHost($query, $search);

        return $query;
    }

    private function buildQueryGroup(QueryBuilder $query, SearchDto $search)
    {
        if (!$search->getGroup()) {
            return;
        }

        $query->andWhere('ps.groupName LIKE :likeQuery')
            ->setParameter('likeQuery', '%'.$search->getGroup().'%')
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

    private function buildQueryHost(QueryBuilder $query, SearchDto $search)
    {
        if (!$search->getHost()) {
            return;
        }
        $query->andWhere('ps.host LIKE :likeQuery')
            ->setParameter('likeQuery', '%'.$search->getHost().'%')
        ;
    }

    private function buildQueryCommand(QueryBuilder $query, SearchDto $search)
    {
        if (!$search->getCommand()) {
            return;
        }
        $query->andWhere('ps.command LIKE :likeQuery')
            ->setParameter('likeQuery', '%'.$search->getCommand().'%')
        ;
    }
}
