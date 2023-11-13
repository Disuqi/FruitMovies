<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchCategory;
use App\Utils\Search\SearchOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MovieRepository extends ServiceEntityRepository
{
    public const BASE_IMAGE_URL = "https://image.tmdb.org/t/p/w1280/";
    public const PAGE_SIZE = 18;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function searchMovies(SearchOptions $options)
    {
        $qb = $this->createQueryBuilder('m');
        $joinedReviewTable = false;
        $hasAverageRating = false;
        $hasReviewCount = false;

        switch($options->orderBy)
        {
            case OrderBy::Title:
                $qb->select('m')
                    ->orderBy('m.title', $options->sortOrder->value);
                break;
            case OrderBy::Rating:
                $qb->select('m, AVG(r.score) as averageRating')
                    ->join('m.reviews', 'r')
                    ->groupBy('m')
                    ->orderBy('averageRating', $options->sortOrder->value);
                $joinedReviewTable = true;
                $hasAverageRating = true;
                break;
            case OrderBy::ReleaseDate:
                $qb->select('m')
                    ->orderBy('m.release_date', $options->sortOrder->value);
                break;
            case OrderBy::Reviews:
                $qb->select('m, COUNT(r.id) as numberOfReviews')
                    ->join('m.reviews', 'r')
                    ->groupBy('m')
                    ->orderBy('numberOfReviews', $options->sortOrder->value);
                $joinedReviewTable = true;
                $hasReviewCount = true;
                break;
            case OrderBy::None:
                throw new \Exception('To be implemented');
        }

        switch($options->additionalOrderBy)
        {
            case OrderBy::Title:
                $qb->addOrderBy('m.title', $options->additionalSortOrder->value);
                break;
            case OrderBy::Rating:
                if(!$hasAverageRating)
                {
                    $qb->addSelect('AVG(r.score) as averageRating');
                    $hasAverageRating = true;
                }

                if(!$joinedReviewTable)
                {
                    $qb->join('m.reviews', 'r')
                        ->groupBy('m');
                    $joinedReviewTable = true;
                }

                $qb->addOrderBy('averageRating', $options->additionalSortOrder->value);
                break;
            case OrderBy::ReleaseDate:
                $qb->addOrderBy('m.release_date', $options->additionalSortOrder->value);
                break;
            case OrderBy::Reviews:
                if(!$hasReviewCount)
                {
                    $qb->addSelect('m, COUNT(r.id) as numberOfReviews');
                    $hasReviewCount = true;
                }
                if(!$joinedReviewTable)
                {
                    $qb->join('m.reviews', 'r')
                        ->groupBy('m');
                    $joinedReviewTable = true;
                }

                $qb->addOrderBy('numberOfReviews', $options->additionalSortOrder->value);
                break;
        }

        if($options->page > 0)
        {
            $qb->setFirstResult(($options->page - 1) * self::PAGE_SIZE)
                ->setMaxResults(self::PAGE_SIZE);
        }

        if($options->startDate != null && $options->endDate != null)
        {
            $qb->where('m.release_date BETWEEN :start AND :end')
                ->setParameter('start', $options->startDate)
                ->setParameter('end', $options->endDate);
        }
        else if($options->startDate != null && $options->endDate == null)
        {
            $qb->where('m.release_date > :start')
                ->setParameter('start', $options->startDate);
        }
        else if($options->startDate == null && $options->endDate != null)
        {
            $qb->where('m.release_date < :end')
                ->setParameter('end', $options->endDate);
        }

        if($options->searchQuery != null)
        {
            $qb->where('m.title LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $options->searchQuery . '%');
        }
        $results = $qb->getQuery()->getResult();

        if(is_array($results[0]))
        {
            return array_map(function ($row) {
                return $row[0];
            }, $results);
        }else
        {
            return $results;
        }
    }
}
