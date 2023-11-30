<?php

namespace App\Controller\Pages;

use App\Form\AddMovieFormType;
use App\Form\OrderMoviesFormType;
use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use App\Utils\Search\OrderMoviesBy;
use App\Utils\Search\MoviesSearchCategory;
use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\SortOrder;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchPages extends AbstractController
{
    private ?MoviesSearchCategory $lastSearchCategory = null;
    private ?MoviesSearchOptions $lastSearchOptions = null;

    #[Route("/search/movie/{slug}/{page}", name:"searchMovie")]
    #[Template("search/searchMovie.html.twig")]
    public function searchMovie(Request $request, MovieRepository $movieRepository, string $slug, int $page = 1) : array
    {
        $searchFormOptions = new MoviesSearchOptions(page: $page);
        $orderForm = $this->createForm(OrderMoviesFormType::class, $searchFormOptions);
        $orderForm->handleRequest($request);
        $searchForm = $this->createForm(SearchFormType::class);
        $addMovieForm = null;

        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(AddMovieFormType::class);

        try
        {
            $category = MoviesSearchCategory::from($slug);
        }catch(\Exception $e)
        {
            $category = null;
        }

        $searchOptions = new MoviesSearchOptions(page: $page);
        switch ($slug)
        {
            case MoviesSearchCategory::Popular->value:
                $searchOptions->orderBy = OrderMoviesBy::Reviews;
                break;
            case MoviesSearchCategory::TopRated->value:
                $searchOptions->orderBy = OrderMoviesBy::Rating;
                break;
            case MoviesSearchCategory::Latest->value:
                $searchOptions->orderBy = OrderMoviesBy::ReleaseDate;
                $searchOptions->startDate = new \DateTime("-2 months");
                $searchOptions->endDate = new \DateTime();
                break;
            case MoviesSearchCategory::Upcoming->value:
                $searchOptions->orderBy = OrderMoviesBy::ReleaseDate;
                $searchOptions->sortOrder = SortOrder::Ascending;
                $searchOptions->startDate = new \DateTime("+1 day");
                break;
            case MoviesSearchCategory::All->value:
                $searchOptions->orderBy = OrderMoviesBy::Title;
                $searchOptions->sortOrder = SortOrder::Ascending;
                break;
            default:
                $searchOptions->searchQuery = $slug;
                break;
        }

        if($orderForm->isSubmitted() && $orderForm->isValid())
        {
            $searchOptions->additionalOrderBy = $searchOptions->orderBy;
            $searchOptions->additionalSortOrder = $searchOptions->sortOrder;
            $searchOptions->orderBy = $searchFormOptions->orderBy;
            $searchOptions->sortOrder = $searchFormOptions->sortOrder;
        }
        elseif($this->lastSearchCategory == $category)
        {
            $searchOptions = $this->lastSearchOptions;
        }
        $searchResult = $movieRepository->searchMovies($searchOptions);
        $this->lastSearchCategory = $category;
        $this->lastSearchOptions = $searchOptions;

        return
            [
                "order_form" => $orderForm,
                "movies" => $searchResult->results,
                "current_page" => $searchResult->current_page,
                "total_pages" => $searchResult->total_pages,
                "slug" => $slug,
                "search_form" => $searchForm,
                "add_movie_form" => $addMovieForm
            ];
    }

    #[Route("/search/user/{slug}/{page}", name:"searchUser")]
    #[Template("search/searchUser.html.twig")]
    public function searchUser(UserRepository $userRepository, string $slug, int $page = 1) : array
    {
        if($slug[0] == "@")
            $slug = substr($slug, 1);
        $searchResult = $userRepository->findByUsername($slug, $page);
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(AddMovieFormType::class)->createView();
        return
            [
                "users" => $searchResult->results,
                "current_page" => $searchResult->current_page,
                "total_pages" => $searchResult->total_pages,
                "slug" => $slug,
                "search_form" => $searchForm,
                "add_movie_form" => $addMovieForm
            ];
    }
}