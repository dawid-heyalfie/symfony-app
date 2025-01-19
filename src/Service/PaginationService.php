<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class PaginationService
{
    public function paginate(QueryBuilder $queryBuilder, array $filters): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Apply pagination to query builder
        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit);

        // Count total results
        $countQuery = clone $queryBuilder;
        try {
            $totalCount = $countQuery
                ->select('COUNT(p.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $totalCount = 0;
        }

        return [
            'page' => $page,
            'limit' => $limit,
            'totalCount' => $totalCount,
        ];
    }
}
