<?php

namespace App\Controller;


use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MoviePageController extends AbstractController
{
    #[Route('/movie/{id}', name:"movie")]
    #[Template('movie.html.twig')]
    public function movie(ReviewRepository $reviewRepository, Movie $movie) : array
    {
        $reviews = $reviewRepository->findBy(["movie" => $movie]);
        return ["movie" => $movie, "reviews" => $reviews];
    }
}