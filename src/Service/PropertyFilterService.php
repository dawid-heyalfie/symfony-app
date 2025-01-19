<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class PropertyFilterService
{
    public function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['title'])) {
            $queryBuilder->andWhere('p.title LIKE :title')
                ->setParameter('title', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['minPrice'])) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }

        if (!empty($filters['maxPrice'])) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }
    }
}
