<?php
namespace App\Controller\RequestHandlers;

use App\Entity\CrewMember;
use App\Entity\Movie;
use App\Entity\MovieCrewMember;
use App\Form\MovieFormType;
use App\Repository\CrewMemberRepository;
use App\Services\Clients\OsmClient;
use App\Services\Clients\TmdbClient;
use App\Services\Errors\ErrorHandler;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesRequestHandler extends AbstractController
{
    #[Route("/cinemas", name: "cinemas")]
    public function findCinemas(Request $request, OsmClient $osm): Response
    {
        $lat = $request->headers->get("lat");
        $long = $request->headers->get("long");
        $range = 1000;
        try
        {
            do
            {
                $cinemas = $osm->findCinemas($lat, $long, $range);
                $range += 1000;
                if($range >= 20000)
                    break;
            }
            while(count($cinemas) < 5);
            return new Response(json_encode($cinemas), Response::HTTP_OK);
        }
        catch(Error $e)
        {
            return new Response("Something went wrong", Response::HTTP_FAILED_DEPENDENCY);
        }
    }

    #[Route("/search_tmdb", name: "searchTmdb")]
    public function searchTmdb(Request $request, TmdbClient $tmdb): Response
    {
        $searchQuery = $request->headers->get("searchQuery");
        $searchResults = $tmdb->searchMovies($searchQuery);
        return new Response(json_encode(["results"=> $searchResults]), Response::HTTP_OK);
    }

    #[Route("/search_movie_directors", name: "searchMovieDirectors")]
    public function searchMovieDirectors(Request $request, TmdbClient $tmdb): Response
    {
        $searchQuery = $request->headers->get("movieTitle");
        if($searchQuery == null)
            return new Response(null, Response::HTTP_BAD_REQUEST);

        $movies = $tmdb->searchMovies($searchQuery);
        $results = [];
        foreach($movies as $movie)
        {
            $director = $tmdb->getDirector($movie->id);
            if($director)
                $results[] = $director;
        }
        return new Response(json_encode(["results"=> $results]), Response::HTTP_OK);
    }

    #[Route("/search_movie_cast", name: "searchMovieCast")]
    public function searchMovieCast(Request $request, TmdbClient $tmdb): Response
    {
        $searchQuery = $request->headers->get("movieTitle");
        $movies = $tmdb->searchMovies($searchQuery);
        $results = [];
        foreach($movies as $movie)
        {
            $cast = $tmdb->getCast($movie->id);
            if($cast)
            {
                foreach($cast as $actor)
                {
                    $results[] = $actor;
                }
            }
        }
        return new Response(json_encode(["results"=> $results]), Response::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    #[Route("/addMovie", name:"addMovie")]
    public function addMovie(Request $request, CrewMemberRepository $crewMemberRepository, EntityManagerInterface $entityManager, ImageSaver $imageSaver) : RedirectResponse
    {
        $movie = new Movie();
        $form = $this->createForm( MovieFormType::class, $movie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if($form->get("release_date")->getData())
                $movie->setReleaseDate(\DateTimeImmutable::createFromMutable($form->get("release_date")->getData()));
            $entityManager->persist($movie);
            $entityManager->flush();

            $coverPhoto = $form->get("cover_photo")->getData();
            if($coverPhoto)
            {
                $imageSaver->saveImage($movie, $coverPhoto);
            }

            $directorName = $form->get("director")->getData();
            if ($directorName)
            {
                $director = $crewMemberRepository->findOneBy(["name" => $directorName, "role" => "director"]);
                if(!$director)
                {
                    $director = new CrewMember();
                    $director->setName($directorName);
                    $director->setRole("director");
                    $entityManager->persist($director);
                }
                $directorRelation = new MovieCrewMember();
                $directorRelation->setCrewMember($director);
                $directorRelation->setMovie($movie);

                $entityManager->persist($directorRelation);
            }

            $actorNames = $form->get("actors")->getData();
            if($actorNames)
            {
                foreach($actorNames as $name)
                {
                    $actor = $crewMemberRepository->findOneBy(["name" => $name, "role" => "actor"]);
                    if(!$actor)
                    {
                        $actor = new CrewMember();
                        $actor->setName($name);
                        $actor->setRole("actor");
                        $entityManager->persist($actor);
                    }
                    $movieActorRelation = new MovieCrewMember();
                    $movieActorRelation->setMovie($movie);
                    $movieActorRelation->setCrewMember($actor);
                    $entityManager->persist($movieActorRelation);
                }
            }
            $entityManager->flush();
            return $this->redirectToRoute("movie", ["id" => $movie->getId()]);
        }
        ErrorHandler::AddFormErrors($request->getSession(), $form);
        return $this->handleRedirect($request);
    }

    #[Route("/removeMovie/{id}", name: "removeMovie")]
    public function removeMovie(Movie $movie, EntityManagerInterface $entityManager, ImageSaver $imageSaver, Request $request): RedirectResponse
    {
        try
        {
            $imageSaver->deleteImage($movie);
            $entityManager->remove($movie);
            $entityManager->flush();
        }catch (Exception|Error $e)
        {
            ErrorHandler::AddError($request->getSession(), "Failed to remove " . $movie->getTitle());
        }
        return $this->redirectToRoute("home");
    }

    private function handleRedirect(Request $request) : RedirectResponse
    {
        $referer = $request->headers->get("referer");
        return new RedirectResponse($referer);
    }
}