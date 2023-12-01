<?php
namespace App\Controller\RequestHandlers;

use App\Entity\CrewMember;
use App\Entity\Movie;
use App\Entity\MovieCrewMember;
use App\Form\AddMovieFormType;
use App\Repository\CrewMemberRepository;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoviesRequestHandler extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route("/addMovie", name:"addMovie")]
    public function addMovie(Request $request, CrewMemberRepository $crewMemberRepository, EntityManagerInterface $entityManager, ImageSaver $imageSaver) : RedirectResponse
    {
        $movie = new Movie();
        $form = $this->createForm( AddMovieFormType::class, $movie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $movie->setReleaseDate(\DateTimeImmutable::createFromMutable($form->get("release_date")->getData()));
            $entityManager->persist($movie);
            $entityManager->flush();

            $coverPhoto = $form->get("cover_photo")->getData();
            if($coverPhoto)
            {
                $imageSaver->saveImage($movie, $coverPhoto);
            }

            $directorName = $form->get("director")->getData();
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

            $actorNames = $form->get("actors")->getData();
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
            $entityManager->flush();
        }
        return $this->redirectToRoute("movie", ["id" => $movie->getId()]);
    }

    #[Route("/deleteMovie/{id}", name: "deleteMovie")]
    public function deleteMovie(Movie $movie, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($movie);
        $entityManager->flush();
        return $this->redirectToRoute("home");
    }
}