<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

enum SearchCategory : string
{
    case Popular = "popular";
    case TopRated = "top_rated";
    case Latest = "latest";
    case Upcoming = "upcoming";
    case All = "all";
}

class SearchController extends AbstractController
{
    #[Route('/search/{slug}', name:"search")]
    #[Template('search.html.twig')]
    public function home(MovieRepository $movieRepository, string $slug) : array
    {
        $movies = [];
        switch ($slug)
        {
            case SearchCategory::Popular:
                $movies = $movieRepository->getPopularMovies();
                break;
            case SearchCategory::TopRated:
                $movies = $movieRepository->getTopRatedMovies();
                break;
            case SearchCategory::Latest:
                $movies = $movieRepository->getLatestMovies();
                break;
            case SearchCategory::Upcoming:
                $movies = $movieRepository->getUpcomingMovies();
                break;
            case SearchCategory::All:
                $movies = $movieRepository->getAll();
                break;
            default:
                $movies = $movieRepository->searchMovie($slug);
                break;
        }
        print(count($movies));
        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "movies" => $movies];
    }
}