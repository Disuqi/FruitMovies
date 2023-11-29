<?php

namespace App\Controller\RequestHandlers;


use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use App\Utils\Search\OrderMoviesBy;
use App\Utils\Search\MoviesSearchCategory;
use App\Utils\Search\MoviesSearchOptions;
use App\Utils\Search\SortOrder;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchRequestHandler extends AbstractController
{
    #[Route("/search", name: "search")]
    public function search(Request $request, LoggerInterface $logger) : Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid())
        {
            $formData = $searchForm->getData();
            $slug = $formData["search_box"];
            $searchType = $formData["search_options"];
            $logger->info("SLUG: " . $slug . " SEARCH TYPE: " . $searchType);
            if($searchType === "movie")
                return $this->redirectToRoute("searchMovie", ["slug" => $slug]);
            else
                return $this->redirectToRoute("searchUser", ["slug" => $slug]);
        }
        return $this->redirectToRoute("home");
    }
}