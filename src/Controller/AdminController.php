<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\AddMovieFormType;
use App\Repository\MovieRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchOptions;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route("/admin", name:"admin")]
    #[Template("profile/admin.html.twig")]
    public function home(MovieRepository $movieRepository) : array
    {
        $movie = new Movie();
        $addMovieForm = $this->createForm(AddMovieFormType::class, $movie);

        return ["addMovieForm" => $addMovieForm];
    }
}