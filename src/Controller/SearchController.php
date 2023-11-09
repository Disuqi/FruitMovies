<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

enum SearchCategory : string
{
    case Popular = "popular";
    case TopRated = "top rated";
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
        $movies = match ($slug) {
            SearchCategory::Popular->value => $movieRepository->getPopularMovies(),
            SearchCategory::TopRated->value => $movieRepository->getTopRatedMovies(),
            SearchCategory::Latest->value => $movieRepository->getLatestMovies(),
            SearchCategory::Upcoming->value => $movieRepository->getUpcomingMovies(),
            SearchCategory::All->value => $movieRepository->getAll(),
            default => $movieRepository->searchMovie($slug),
        };
        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "movies" => $movies];
    }
}