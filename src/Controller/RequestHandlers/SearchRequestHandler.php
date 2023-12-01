<?php

namespace App\Controller\RequestHandlers;


use App\Form\SearchFormType;
use App\Utils\Errors\ErrorHandler;
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
        ErrorHandler::AddFormErrors($searchForm);
        return $this->redirectToRoute("home");
    }
}