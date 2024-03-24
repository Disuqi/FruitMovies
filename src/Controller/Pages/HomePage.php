<?php

namespace App\Controller\Pages;

use App\Form\MovieFormType;
use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Utils\Errors\ErrorHandler;
use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\OrderMoviesBy;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomePage extends AbstractController
{
    #[Route("/", name:"home")]
    #[Template("home.html.twig")]
    public function home(MovieRepository $movieRepository, Request $request, LoggerInterface $logger) : array
    {
        $options = new MoviesSearchOptions(OrderMoviesBy::Reviews, additionalOrderBy: OrderMoviesBy::Rating, startDate: new \DateTime("-1 month"), endDate: new \DateTime());
        $juiciestPicks = $movieRepository->searchMovies($options)->results;
        while(count($juiciestPicks) <= 10)
        {
            $options->startDate->modify("-1 month");
            $juiciestPicks = $movieRepository->searchMovies($options)->results;
        }
        $movieOfTheMonth = array_shift($juiciestPicks);
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(MovieFormType::class)->createView();
        return [
            "pickOfTheMonth" => $movieOfTheMonth,
            "juiciestPicks" => $juiciestPicks,
            "search_form" => $searchForm,
            "add_movie_form" => $addMovieForm,
            "errors" => ErrorHandler::GetAndClearErrors($request->getSession())
        ];
    }
}