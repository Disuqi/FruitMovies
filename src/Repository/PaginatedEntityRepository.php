<?php

namespace App\Repository;

use App\Utils\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class PaginatedEntityRepository extends ServiceEntityRepository
{
    public function getPage(int $page = 1) : SearchResult
    {
        $totalPages = $this->getTotalPages();

        if($page < 1)
            return new SearchResult([], 0, $totalPages);

        if($totalPages == 0)
            return new SearchResult([], 0, 0);

        $results = $this->createQueryBuilder("m")
            ->select("m")
            ->setFirstResult(($page - 1) * PAGE_SIZE)
            ->setMaxResults(PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return new SearchResult($results, $page, $totalPages);
    }

    public function getTotalPages(): int
    {
        $totalCount = $this->createQueryBuilder("m")
            ->select("m")
            ->select("COUNT(DISTINCT m.id)")
            ->getQuery()
            ->getSingleScalarResult();

        return ceil($totalCount/PAGE_SIZE);
    }
}