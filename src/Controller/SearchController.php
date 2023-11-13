<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchCategory;
use App\Utils\Search\SearchOptions;
use App\Utils\Search\SortOrder;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/{slug}/{page}', name:"search")]
    #[Template('search.html.twig')]
    public function search(MovieRepository $movieRepository, Request $request, string $slug, int $page = 1)
    {
        $searchOptions = match ($slug) {
            SearchCategory::Popular->value => new SearchOptions(OrderBy::Reviews, page: $page),
            SearchCategory::TopRated->value => new SearchOptions(OrderBy::Rating, page: $page),
            SearchCategory::Latest->value => new SearchOptions(OrderBy::ReleaseDate, startDate: new \DateTime('-2 months'), endDate: new \DateTime(), page: $page),
            SearchCategory::Upcoming->value => new SearchOptions(OrderBy::ReleaseDate, orderSort: SortOrder::Ascending, startDate: new \DateTime( '+1 day'), page: $page),
            SearchCategory::All->value => new SearchOptions(OrderBy::Title, orderSort: SortOrder::Ascending),
            default => new SearchOptions(page: $page, searchQuery: $slug),
        };
        $searchResult = $movieRepository->searchMovies($searchOptions);
        return ["movies" => $searchResult->results, "current_page" => $searchResult->current_page, "total_pages" => $searchResult->total_pages, "slug" => $slug];
    }
}