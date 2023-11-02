<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public const BASE_IMAGE_URL = "https://image.tmdb.org/t/p/w500/";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function getTopRatedThisWeek()
    {
        $qb = $this->createQueryBuilder('m')
            ->select('movie, AVG(r.score) as averageRating, COUNT(r.id) as numberOfReviews')
            ->from(Movie::class, 'movie')
            ->join('movie.reviews', 'r')
            ->where('r.date_reviewed BETWEEN :start AND :end')
            ->groupBy('movie')
            ->orderBy('numberOfReviews', 'DESC')
            ->addOrderBy('averageRating', 'DESC')
            ->setParameter('start', new \DateTime('-7 days'))
            ->setParameter('end', new \DateTime());

        // Setting the custom DTO as the result class
        $results = $qb->getQuery()->getResult();

        // Map results to DTO
        return array_map(function($result) {
            return new MovieRatingDTO($result[0], (float) $result['averageRating'], (int) $result['numberOfReviews']);
        }, $results);
    }
}

class MovieRatingDTO
{
    public readonly Movie $movie;
    public readonly float $averageRating;
    public readonly int $numberOfReviews;

    public function __construct(Movie $movie, float $averageRating, int $numberOfReviews)
    {
        $this->movie = $movie;
        $this->averageRating = $averageRating;
        $this->numberOfReviews = $numberOfReviews;
    }
}
