<?php

namespace App\Controller\RequestHandlers;


use App\Form\SearchFormType;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchCategory;
use App\Utils\Search\SearchOptions;
use App\Utils\Search\SortOrder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchRequestHandler extends AbstractController
{
    #[Route("/search", name: "search")]
    public function search(Request $request) : Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid())
        {
            $formData = $searchForm->getData();
            $slug = $formData["search_box"];
            $searchType = $formData["search_options"];
            if($searchType === "movie")
                return $this->redirectToRoute("searchUser", ["slug" => $slug]);
            else
                return $this->redirectToRoute("searchMovie", ["movie" => $slug]);
        }
        return $this->redirectToRoute("home");
    }
}