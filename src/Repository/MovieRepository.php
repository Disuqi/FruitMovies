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
    public const BASE_IMAGE_URL = "https://image.tmdb.org/t/p/w1280/";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function getTopRatedThisWeek()
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, AVG(r.score) as averageRating, COUNT(r.id) as numberOfReviews')
            ->join('m.reviews', 'r')
            ->where('r.date_reviewed BETWEEN :start AND :end')
            ->groupBy('m')
            ->orderBy('averageRating', 'DESC')
            ->addOrderBy('numberOfReviews', 'DESC')
            ->setParameter('start', new \DateTime('-2 weeks'))
            ->setParameter('end', new \DateTime());

        $results = $qb->getQuery()->getResult();

        return array_map(function ($row) {
            return $row[0];
        }, $results);
    }

    public function getTopRatedMovies()
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, AVG(r.score) as averageRating, COUNT(r.id) as numberOfReviews')
            ->join('m.reviews', 'r')
            ->groupBy('m')
            ->orderBy('numberOfReviews', 'DESC')
            ->addOrderBy('averageRating', 'DESC');

        $results = $qb->getQuery()->getResult();

        return array_map(function ($row) {
            return $row[0];
        }, $results);
    }

    public function getPopularMovies()
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, COUNT(r.id) as reviewCount')
            ->join('m.reviews', 'r')
            ->groupBy('m')
            ->orderBy('reviewCount', 'DESC');

        $results = $qb->getQuery()->getResult();

        return array_map(function ($row) {
            return $row[0];
        }, $results);
    }


    public function getLatestMovies()
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.release_date', 'DESC')
            ->where('m.release_date BETWEEN :start AND :end')
            ->setParameter('start', new \DateTime('-60 days'))
            ->setParameter('end', new \DateTime());;

        return $qb->getQuery()->getResult();
    }

    public function getUpcomingMovies(): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.release_date > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.release_date', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function searchMovie(string $searchQuery): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.title LIKE :searchTerm')
            ->orWhere('m.overview LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchQuery . '%')
            ->orderBy('m.release_date', 'DESC');

        return  $qb->getQuery()->getResult();
    }

    public function getAll() : array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.release_date', 'DESC');
        return  $qb->getQuery()->getResult();
    }
}
