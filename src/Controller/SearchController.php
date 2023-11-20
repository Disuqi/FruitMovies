<?php

namespace App\Controller;


use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchCategory;
use App\Utils\Search\SearchOptions;
use App\Utils\Search\SortOrder;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route("/search", name: "search")]
    public function search(Request $request, MovieRepository $movieRepository, UserRepository $userRepository, LoggerInterface $logger) : Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid())
        {
            $formData = $searchForm->getData();
            $slug = $formData["search_box"];
            $searchType = $formData["search_options"];
            $logger->info("SERCH TYPE: ". $searchType);
            if($searchType === "movie")
                return $this->searchMovie($movieRepository, $slug);
            else
                return $this->searchUser($userRepository, $slug);
        }
        return $this->redirectToRoute("home");
    }

    #[Route("/search/movie/{slug}/{page}", name:"searchMovie")]
    public function searchMovie(MovieRepository $movieRepository, string $slug, int $page = 1) : Response
    {
        $searchOptions = match ($slug) {
            SearchCategory::Popular->value => new SearchOptions(OrderBy::Reviews, page: $page),
            SearchCategory::TopRated->value => new SearchOptions(OrderBy::Rating, page: $page),
            SearchCategory::Latest->value => new SearchOptions(OrderBy::ReleaseDate, startDate: new \DateTime("-2 months"), endDate: new \DateTime(), page: $page),
            SearchCategory::Upcoming->value => new SearchOptions(OrderBy::ReleaseDate, orderSort: SortOrder::Ascending, startDate: new \DateTime( "+1 day"), page: $page),
            SearchCategory::All->value => new SearchOptions(OrderBy::Title, orderSort: SortOrder::Ascending),
            default => new SearchOptions(page: $page, searchQuery: $slug),
        };
        $searchResult = $movieRepository->searchMovies($searchOptions);
        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render("search/searchMovie.html.twig", 
            [
                "movies" => $searchResult->results, 
                "current_page" => $searchResult->current_page, 
                "total_pages" => $searchResult->total_pages,
                "slug" => $slug,
                "search_form" => $searchForm
            ]);
    }

    #[Route("/search/user/{slug}/{page}", name:"searchUser")]
    public function searchUser(UserRepository $userRepository, string $slug, int $page = 1) : Response
    {
        if($slug[0] == "@")
            $slug = substr($slug, 1);
        $searchResult = $userRepository->findByUsername($slug, $page);
        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render("search/searchUser.html.twig",
            [
                "users" => $searchResult->results,
                "current_page" => $searchResult->current_page,
                "total_pages" => $searchResult->total_pages,
                "slug" => $slug,
                "search_form" => $searchForm]);
    }
}