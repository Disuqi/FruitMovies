<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Utils;
use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\OrderMoviesBy;
use App\Utils\Search\SearchResult;
use App\Utils\Search\SortOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MovieRepository extends ServiceEntityRepository
{
    private const AVERAGE_RATING_NAME = "average_rating";
    private const REVIEW_COUNT_NAME = "review_count";
    private const REVIEW_TABLE_ALIAS = "r";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function getTotalPages() : int
    {
        $totalCount = $this->createQueryBuilder("m")
            ->select("COUNT(DISTINCT m.id)")
            ->getQuery()
            ->getSingleScalarResult();
        return ceil($totalCount/PAGE_SIZE);
    }

    public function searchMovies(MoviesSearchOptions $options)
    {
        $qb = $this->createQueryBuilder("m");
        $qb->select("m");
        $this->queryOrderBy($qb, $options->orderBy, $options->sortOrder);
        $this->queryOrderBy($qb, $options->additionalOrderBy, $options->additionalSortOrder);
        $this->setQueryDateRange($qb, $options->startDate, $options->endDate);
        $joinedReview = $this->joinReviewTableIfNeeded($qb);

        $countQb = $this->createQueryBuilder("m");
        $countQb->select("COUNT(DISTINCT m.id)");
        $this->setQueryDateRange($countQb, $options->startDate, $options->endDate);
        if($joinedReview)
            $countQb->innerJoin("m.reviews", self::REVIEW_TABLE_ALIAS);
        if($options->searchQuery != null)
        {
            $qb->where("m.title LIKE :searchTerm")
                ->setParameter("searchTerm", "%" . $options->searchQuery . "%");
            $countQb->where("m.title LIKE :searchTerm")
                ->setParameter("searchTerm", "%" . $options->searchQuery . "%");
        }


        $totalCount = $countQb->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalCount/PAGE_SIZE);

        if($totalPages == 0)
        {
            return new SearchResult([], 0, 0);
        }

        if($options->page > $totalPages)
        {
            throw new \OutOfRangeException();
        }

        if($options->page > 0)
        {
            $qb->setFirstResult(($options->page - 1) * PAGE_SIZE)
                ->setMaxResults(PAGE_SIZE);
        }
        $results = $qb->getQuery()->getResult();

        if(count($results) > 0 and is_array($results[0]))
        {
            $results = array_map(function ($row) {
                return $row[0];
            }, $results);
        }

        return new SearchResult($results, $options->page, $totalPages);
    }

    private function queryOrderBy(QueryBuilder $qb, OrderMoviesBy $orderBy, SortOrder $sortOrder)
    {
        switch($orderBy)
        {
            case OrderMoviesBy::Title:
                $qb->addOrderBy("m.title", $sortOrder->value);
                break;
            case OrderMoviesBy::Rating:
                $qb->addSelect("AVG(". self::REVIEW_TABLE_ALIAS .".score) as " . self::AVERAGE_RATING_NAME)
                    ->addOrderBy(self::AVERAGE_RATING_NAME, $sortOrder->value);
                break;
            case OrderMoviesBy::ReleaseDate:
                $qb->addOrderBy("m.release_date", $sortOrder->value);
                break;
            case OrderMoviesBy::Reviews:
                $qb->addSelect("COUNT(". self::REVIEW_TABLE_ALIAS .".id) as " . self::REVIEW_COUNT_NAME)
                    ->addOrderBy(self::REVIEW_COUNT_NAME, $sortOrder->value);
                break;
        }
    }

    private function setQueryDateRange(QueryBuilder $qb, ?\DateTime $startDate, ?\DateTime $endDate)
    {
        if($startDate != null && $endDate != null)
        {
            $qb->where("m.release_date BETWEEN :start AND :end")
                ->setParameter("start", $startDate)
                ->setParameter("end", $endDate);
        }
        else if($startDate != null && $endDate == null)
        {
            $qb->where("m.release_date > :start")
                ->setParameter("start", $startDate);
        }
        else if($startDate == null && $endDate != null)
        {
            $qb->where("m.release_date < :end")
                ->setParameter("end", $endDate);
        }
    }

    private function joinReviewTableIfNeeded(QueryBuilder $qb) : bool
    {
        $dql = $qb->getDQL();
        if(str_contains($dql, self::REVIEW_TABLE_ALIAS . "."))
        {
            $qb->join("m.reviews", self::REVIEW_TABLE_ALIAS)
                ->groupBy("m");
            return true;
        }
        return false;
    }
}
