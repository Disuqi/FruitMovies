<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchOptions;
use App\Utils\Search\SearchResult;
use App\Utils\Search\SortOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MovieRepository extends ServiceEntityRepository
{
    public const BASE_IMAGE_URL = "https://image.tmdb.org/t/p/w1280/";
    public const PAGE_SIZE = 18;
    private const AVERAGE_RATING_NAME = 'average_rating';
    private const REVIEW_COUNT_NAME = 'review_count';
    private const REVIEW_TABLE_ALIAS = 'r';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function searchMovies(SearchOptions $options)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select("m");
        $this->queryOrderBy($qb, $options->orderBy, $options->sortOrder);
        $this->queryOrderBy($qb, $options->additionalOrderBy, $options->additionalSortOrder);
        $this->setQueryDateRange($qb, $options->startDate, $options->endDate);
        $this->joinReviewTableIfNeeded($qb);
        if($options->searchQuery != null)
        {
            $qb->where('m.title LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $options->searchQuery . '%');
        }

        $countQb = $this->createQueryBuilder('m');
        $countQb->select('COUNT(m.id)');
        $this->setQueryDateRange($qb, $options->startDate, $options->endDate);
        $totalCount = $countQb->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalCount/self::PAGE_SIZE);

        if($options->page > $totalPages)
        {
            throw new \OutOfRangeException();
        }

        if($options->page > 0)
        {
            $qb->setFirstResult(($options->page - 1) * self::PAGE_SIZE)
                ->setMaxResults(self::PAGE_SIZE);
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

    private function queryOrderBy(QueryBuilder $qb, OrderBy $orderBy, SortOrder $sortOrder)
    {
        switch($orderBy)
        {
            case OrderBy::Title:
                $qb->addOrderBy('m.title', $sortOrder->value);
                break;
            case OrderBy::Rating:
                $qb->addSelect('AVG('. self::REVIEW_TABLE_ALIAS .'.score) as ' . self::AVERAGE_RATING_NAME)
                    ->addOrderBy(self::AVERAGE_RATING_NAME, $sortOrder->value);
                break;
            case OrderBy::ReleaseDate:
                $qb->addOrderBy('m.release_date', $sortOrder->value);
                break;
            case OrderBy::Reviews:
                $qb->addSelect('COUNT('. self::REVIEW_TABLE_ALIAS .'.id) as ' . self::REVIEW_COUNT_NAME)
                    ->addOrderBy(self::REVIEW_COUNT_NAME, $sortOrder->value);
                break;
        }
    }

    private function setQueryDateRange(QueryBuilder $qb, ?\DateTime $startDate, ?\DateTime $endDate)
    {
        if($startDate != null && $endDate != null)
        {
            $qb->where('m.release_date BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);
        }
        else if($startDate != null && $endDate == null)
        {
            $qb->where('m.release_date > :start')
                ->setParameter('start', $startDate);
        }
        else if($startDate == null && $endDate != null)
        {
            $qb->where('m.release_date < :end')
                ->setParameter('end', $endDate);
        }
    }

    private function joinReviewTableIfNeeded(QueryBuilder $qb)
    {
        $dql = $qb->getDQL();
        if(str_contains($dql, self::REVIEW_TABLE_ALIAS . '.'))
        {
            $qb->join('m.reviews', self::REVIEW_TABLE_ALIAS)
                ->groupBy('m');
        }
    }
}
