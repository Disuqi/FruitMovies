<?php

namespace App\Controller\Pages;

use App\Entity\CrewMember;
use App\Form\MovieFormType;
use App\Form\SearchFormType;
use App\Repository\MovieCrewMemberRepository;
use App\Repository\MovieRepository;
use App\Services\Clients\TmdbClient;
use App\Services\Errors\ErrorHandler;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CrewMemberPage extends AbstractController
{
    private MovieCrewMemberRepository $movieCrewMemberRepository;
    private MovieRepository $movieRepository;

    public function __construct(MovieCrewMemberRepository $movieCrewMemberRepository, MovieRepository $movieRepository)
    {
        $this->movieCrewMemberRepository = $movieCrewMemberRepository;
        $this->movieRepository = $movieRepository;
    }

    #[Route("/crewMember/{id}", name:"crewMember")]
    #[Template("crewMember.html.twig")]
    public function crewMember(Request $request, CrewMember $crewMember, TmdbClient $tmdb): array
    {
        $relations = $this->movieCrewMemberRepository->findBy(["crewMember" => $crewMember->getId()]);
        $tmdbData = $tmdb->findPerson($crewMember->getName(), array_map(fn($relation) => $relation->getMovie()->getTitle(), $relations));

        $searchForm = $this->createForm(SearchFormType::class)->createView();

        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(MovieFormType::class)->createView();

        return ["crew_member" => $crewMember,
                "search_form" => $searchForm,
                "add_movie_form" => $addMovieForm,
                "tmdb_data" => $tmdbData,
                "errors" => ErrorHandler::GetAndClearErrors($request->getSession())];
    }
}
