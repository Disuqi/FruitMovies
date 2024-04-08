<?php

namespace App\Controller\Api;

use App\Entity\Movie;
use App\Entity\MovieCrewMember;
use App\Form\MovieApiFormType;
use App\Repository\CrewMemberRepository;
use App\Repository\MovieCrewMemberRepository;
use App\Repository\MovieRepository;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MoviesAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;

    public function __construct(EntityManagerInterface $entityManager, MovieRepository $movieRepository, CrewMemberRepository $crewMemberRepository, MovieCrewMemberRepository $movieCrewMemberRepository)
    {
        $this->entityManager = $entityManager;
        $this->movieRepository = $movieRepository;
    }

    #[Rest\Get("api/v1/movies", name: "getMovies")]
    public function getMovies(Request $request): View
    {
        $contents = json_decode($request->getContent());
        $page = 1;
        if(isset($contents->page))
            $page = $contents->page;

        $totalPages = $this->movieRepository->getTotalPages();
        if($page > $totalPages)
            return View::create("Out of range", Response::HTTP_NOT_FOUND);

        if(!$page || $page == "")
            $page = 1;

        $searchResult = $this->movieRepository->getPage($page);
        return View::create($searchResult, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/movies/{id}", name: "getMovie")]
    public function getMovie(int $id): View
    {
        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        return View::create($movie, Response::HTTP_OK);
    }

    #[Rest\Post("api/v1/movies", name: "postMovie")]
    public function postMovie(Request $request): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can create movies", Response::HTTP_FORBIDDEN);

        $movie = new Movie();
        $form = $this->createForm( MovieApiFormType::class, $movie);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $existingMovie = $this->movieRepository->findOneBy(["title" => $movie->getTitle(), "overview" => $movie->getOverview()]);
            if($existingMovie)
                return View::create("Movie already exists", Response::HTTP_CONFLICT)->setLocation("/api/v1/movies/" . $existingMovie->getId());

            try
            {
                $date = new \DateTimeImmutable($data["release_date"]);
            }
            catch(Exception $e)
            {
                return View::create("Invalid date. It should be in this format DD-MM-YYYY", Response::HTTP_BAD_REQUEST);
            }
            $movie->setReleaseDate($date);
            $this->entityManager->persist($movie);
            $this->entityManager->flush();

            return View::create("Successfully added Movie",  Response::HTTP_CREATED)->setLocation("/api/v1/movies/" . $movie->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Rest\Put("api/v1/movies/{id}", name: "putMovie")]
    public function putMovie(Request $request, int $id) : View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can modify movies", Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        $form = $this->createForm(MovieApiFormType::class, $movie);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->entityManager->persist($movie);
            $this->entityManager->flush();
            return View::create("Successfully updated Movie", Response::HTTP_ACCEPTED);
        }
        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    #[Rest\Post("api/v1/movies/{id}/image", name:"postMovieImage")]
    public function postMovieImage(Request $request, ImageSaver $imageSaver, int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can modify movies", Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        $cover_photo = $request->files->get("cover_photo");
        if(!$cover_photo)
            return View::create("No image uploaded or incorrectly labelled", Response::HTTP_BAD_REQUEST);

        try
        {
            $imageSaver->saveImage($movie, $cover_photo);
        }
        catch(\Error|\Exception $e)
        {
            return View::create("Invalid image file. You can only upload .png, .jpeg or .jpg image files",Response::HTTP_BAD_REQUEST);
        }

        return View::create("Successfully saved movie image", Response::HTTP_OK);
    }

    #[Rest\Delete("api/v1/movies/{id}", name: "deleteMovie")]
    public function deleteMovie(int $id) : View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can delete movies", Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        $this->entityManager->remove($movie);
        $this->entityManager->flush();

        return View::create("Successfully deleted movie", Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/movies/{id}/crewMembers", name: "getMovieCrewMembers")]
    public function getMovieCrewMembers(int $id): View
    {
        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        return View::create(["director" => $movie->getDirector(), "actors" => $movie->getActors()], Response::HTTP_OK);
    }

    #[Rest\Put("api/v1/movies/{id}/crewMembers", name:"putMovieCrewMembers")]
    public function putMovieCrewMembers(Request $request, CrewMemberRepository $crewMemberRepository, MovieCrewMemberRepository $movieCrewMemberRepository, int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can edit movies", Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create("Movie not found", Response::HTTP_NOT_FOUND);

        $data = json_decode($request->getContent());
        $existingRelations = $movieCrewMemberRepository->findBy(["movie" => $movie->getId()]);
        $newRelations = [];

        if(!empty($data->director))
        {
            if(is_array($data->director))
                return View::create("A movie can only have one director", Response::HTTP_BAD_REQUEST);

            if(empty($data->director->id))
                return View::create("Missing Director id. Only existing directors can be added", Response::HTTP_BAD_REQUEST);

            $director = $crewMemberRepository->find($data->director->id);
            if(!$director)
                return View::create("Director not found", Response::HTTP_NOT_FOUND);

            $movieDirectorRelation = new MovieCrewMember();
            $movieDirectorRelation->setMovie($movie);
            $movieDirectorRelation->setCrewMember($director);

            $newRelations[] = $movieDirectorRelation;
        }

        if(!empty($data->actors))
        {
            if(!is_array($data->actors))
                $data->actors = [$data->actors];

            foreach($data->actors as $actorData)
            {
                if(empty($actorData->id))
                    return View::create("One or more actors did not have an id. Only existing actors can be added", Response::HTTP_BAD_REQUEST);

                $actor = $crewMemberRepository->find($actorData->id);
                if(!$actor)
                    return View::create("Actor not found. ID: " . $actorData->id, Response::HTTP_NOT_FOUND);

                $movieActorRelation = new MovieCrewMember();
                $movieActorRelation->setMovie($movie);
                $movieActorRelation->setCrewMember($actor);

                $newRelations[] = $movieActorRelation;
            }
        }

        foreach ($existingRelations as $existingRelation)
        {
            $this->entityManager->remove($existingRelation);
        }
        $this->entityManager->flush();

        foreach ($newRelations as $newRelation)
        {
            $this->entityManager->persist($newRelation);
        }
        $this->entityManager->flush();

        return View::create(null, Response::HTTP_OK)->setLocation("api/v1/movie/" . $movie->getId() . "/crewMembers");
    }
}