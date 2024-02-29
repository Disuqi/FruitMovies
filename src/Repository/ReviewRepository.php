<?php

namespace App\Repository;

use App\Entity\Review;
use App\Utils\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getReviewsPage(int $page = 1) : SearchResult
    {
        $qb = $this->createQueryBuilder("m")
            ->select("m")
            ->setFirstResult(($page - 1) * PAGE_SIZE)
            ->setMaxResults(PAGE_SIZE);
        $reviews = $qb->getQuery()->getResult();

        $totalPages = $this->getTotalPages();

        if($totalPages == 0)
        {
            return new SearchResult([], 0, 0);
        }
        return new SearchResult($reviews, $page, $totalPages);
    }

    public function getTotalPages() : int
    {
        $totalCount = $this->createQueryBuilder("m")
            ->select("m")->select("COUNT(DISTINCT m.id)")->getQuery()->getSingleScalarResult();
        return ceil($totalCount/PAGE_SIZE);
    }
}
