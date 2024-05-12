<?php

namespace App\Controller\Pages;

use App\Form\MovieFormType;
use App\Form\SearchFormType;
use App\Services\Errors\ErrorHandler;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiDashboardPage extends AbstractController
{
    #[Route("/api-dashboard", name: "api-dashboard")]
    #[Template("api_dashboard.html.twig")]
    public function apiDocs(Request $request): array
    {
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(MovieFormType::class)->createView();
        return [
            "search_form" => $searchForm,
            "add_movie_form" => $addMovieForm,
            "errors" => ErrorHandler::GetAndClearErrors($request->getSession())
        ];
    }
}