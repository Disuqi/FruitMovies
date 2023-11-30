<?php

namespace App\Controller\Pages;

use App\Acme\ImageHandlerBundle\AcmeImageHandlerBundle;
use App\Form\AddMovieFormType;
use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\OrderMoviesBy;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomePage extends AbstractController
{
    #[Route("/", name:"home")]
    #[Template("home.html.twig")]
    public function home(MovieRepository $movieRepository, AcmeImageHandlerBundle $imageHandlerBundle) : array
    {
        $imageHandlerBundle->saveImage("data", "entity");
        $options = new MoviesSearchOptions(OrderMoviesBy::Reviews, additionalOrderBy: OrderMoviesBy::Rating, startDate: new \DateTime("-1 month"), endDate: new \DateTime());
        $juiciestPicks = $movieRepository->searchMovies($options)->results;
        $movieOfTheMonth = array_shift($juiciestPicks);
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(AddMovieFormType::class)->createView();
        return [
            "pickOfTheMonth" => $movieOfTheMonth,
            "juiciestPicks" => $juiciestPicks,
            "search_form" => $searchForm,
            "add_movie_form" => $addMovieForm];
    }
}