<?php

namespace App\Tests\Service;

use App\Service\PaginationService;
use App\Service\PropertyFilterService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
    }

    public function testPaginationAppliesOffsetAndLimit(): void
    {
        $paginationService = new PaginationService();
        $filters = ['page' => 2, 'limit' => 5];

        $this->queryBuilder->expects(self::once())
            ->method('setFirstResult')
            ->with(5)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setMaxResults')
            ->with(5)
            ->willReturnSelf();

        $paginationService->paginate($this->queryBuilder, $filters);
    }

    public function testPaginationDefaultValues(): void
    {
        $paginationService = new PaginationService();
        $filters = []; // No pagination parameters

        $this->queryBuilder->expects(self::once())
            ->method('setFirstResult')
            ->with(0)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setMaxResults')
            ->with(10)
            ->willReturnSelf();

        $paginationService->paginate($this->queryBuilder, $filters);
    }

    public function testPaginationCalculatesTotalCount(): void
    {
        $paginationService = new PaginationService();
        $filters = ['page' => 1, 'limit' => 10];

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->method('getSingleScalarResult')->willReturn(50);

        $clonedQueryBuilder = $this->createMock(QueryBuilder::class);
        $clonedQueryBuilder->method('select')->with('COUNT(p.id)')->willReturnSelf();
        $clonedQueryBuilder->method('getQuery')->willReturn($mockQuery);

        $this->queryBuilder->expects(self::once())
            ->method('setFirstResult')
            ->with(0)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setMaxResults')
            ->with(10)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('select')
            ->willReturn($clonedQueryBuilder);

        $result = $paginationService->paginate($this->queryBuilder, $filters);
        self::assertEquals(50, $result['totalCount']);
    }

    public function testPaginationHandlesNoResults(): void
    {
        $paginationService = new PaginationService();
        $filters = ['page' => 1, 'limit' => 10];

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->method('getSingleScalarResult')->willThrowException(new \Doctrine\ORM\NoResultException());

        $clonedQueryBuilder = $this->createMock(QueryBuilder::class);
        $clonedQueryBuilder->method('select')->with('COUNT(p.id)')->willReturnSelf();
        $clonedQueryBuilder->method('getQuery')->willReturn($mockQuery);

        $this->queryBuilder->expects(self::once())
            ->method('setFirstResult')
            ->with(0)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setMaxResults')
            ->with(10)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('select')
            ->willReturn($clonedQueryBuilder);

        $result = $paginationService->paginate($this->queryBuilder, $filters);
        self::assertEquals(0, $result['totalCount']);
    }

    public function testFilterByTitle(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = ['title' => 'Luxury'];

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.title LIKE :title')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('title', '%Luxury%')
            ->willReturnSelf();

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);
    }

    public function testFilterByMinPrice(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = ['minPrice' => 100000];

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.price >= :minPrice')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('minPrice', 100000)
            ->willReturnSelf();

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);
    }

    public function testFilterByMaxPrice(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = ['maxPrice' => 500000];

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.price <= :maxPrice')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('maxPrice', 500000)
            ->willReturnSelf();

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);
    }

    public function testApplyNoFilters(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = []; // No filters

        $this->queryBuilder->expects(self::never())->method('andWhere');
        $this->queryBuilder->expects(self::never())->method('setParameter');

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);
    }

    public function testPaginationDefaults(): void
    {
        $paginationService = new PaginationService();

        $this->queryBuilder->expects(self::once())
            ->method('setFirstResult')
            ->with(0)
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setMaxResults')
            ->with(10)
            ->willReturnSelf();

        $paginationService->paginate($this->queryBuilder, []);
    }

    public function testSingleTitleFilter(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = ['title' => 'Simple Title'];

        $this->queryBuilder->expects(self::once())
            ->method('andWhere')
            ->with('p.title LIKE :title')
            ->willReturnSelf();

        $this->queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('title', '%Simple Title%')
            ->willReturnSelf();

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);
    }


    public function testApplyFilters(): void
    {
        $propertyFilterService = new PropertyFilterService();
        $filters = [
            'title' => 'Luxury',
            'minPrice' => 100000,
            'maxPrice' => 500000,
        ];

        $andWhereCalls = [];
        $setParameterCalls = [];

        $this->queryBuilder->expects(self::exactly(3))
            ->method('andWhere')
            ->willReturnCallback(function ($arg) use (&$andWhereCalls) {
                $andWhereCalls[] = $arg;
                return $this->queryBuilder;
            });

        // Mock `setParameter` to track arguments
        $this->queryBuilder->expects(self::exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function ($key, $value) use (&$setParameterCalls) {
                $setParameterCalls[] = [$key, $value];
                return $this->queryBuilder;
            });

        $propertyFilterService->applyFilters($this->queryBuilder, $filters);

        self::assertEquals(
            ['p.title LIKE :title', 'p.price >= :minPrice', 'p.price <= :maxPrice'],
            $andWhereCalls
        );

        self::assertEquals(
            [
                ['title', '%Luxury%'],
                ['minPrice', 100000],
                ['maxPrice', 500000],
            ],
            $setParameterCalls
        );
    }

}
