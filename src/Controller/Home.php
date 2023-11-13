<?php

namespace App\Controller;


use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchOptions;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Home extends AbstractController
{
    #[Route('/', name:"home")]
    #[Template('home.html.twig')]
    public function home(MovieRepository $movieRepository) : array
    {
        $juiciestPicks = $movieRepository->searchMovies(new SearchOptions(OrderBy::Rating, additionalOrderBy: OrderBy::Reviews, startDate: new \DateTime('-1 month')));
        $movieOfTheMonth = array_shift($juiciestPicks);

        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "pickOfTheMonth" => $movieOfTheMonth, "juiciestPicks" => $juiciestPicks];
    }

    #[Route('/movie/{id}', name:"movie")]
    #[Template('movie.html.twig')]
    public function movie(ReviewRepository $reviewRepository, Movie $movie) : array
    {
        $reviews = $reviewRepository->findBy(["movie" => $movie]);
        return ["baseImageUrl" => MovieRepository::BASE_IMAGE_URL, "movie" => $movie, "reviews" => $reviews];
    }
}