<?php
namespace App\Controller;

use App\Entity\Movie;
use App\Form\AddMovieFormType;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    #[Route("/addMovie", name:"addMovie")]
    public function addMovie(Request $request, EntityManagerInterface $entityManager) : RedirectResponse
    {
        $movie = new Movie();
        $form = $this->createForm( AddMovieFormType::class, $movie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($movie);
            $entityManager->flush();

            $coverPhoto = $form->get("cover_photo")->getData();
            if($coverPhoto)
            {
                try
                {
                    $dir = $movie->getImagesDirectoryPath();
                    $filename = "mainCoverPhoto." . $coverPhoto->guessExtension();
                    $coverPhoto->move($dir, $filename);
                    $movie->setCoverPhoto($dir . $filename);
                    $entityManager->flush();
                }catch(FileException $e)
                {
                    print($e);
                }
            }
        }
        return $this->redirectToRoute("movie", ["id" => $movie->getId()]);
    }
}