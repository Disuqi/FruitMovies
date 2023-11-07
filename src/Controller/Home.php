<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Home extends AbstractController
{
    #[Route('/', name:"home")]
    #[Template('home.html.twig')]
    public function home(MovieRepository $movieRepository) : array
    {
        $juiciestPicks = $movieRepository->getTopRatedThisWeek();
        $movieOfTheWeek = array_shift($juiciestPicks);

        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "pickOfTheWeek" => $movieOfTheWeek, "juiciestPicks" => $juiciestPicks];
    }

    #[Route('/movie/{id}', name:"movie")]
    #[Template('movie.html.twig')]
    public function movie(MovieRepository $movieRepository, ReviewRepository $reviewRepository, int $id) : array
    {
        $movie = $movieRepository->find($id);
        $reviews = $reviewRepository->findBy(["movie" => $movie]);
        return ["baseImageUrl" => MovieRepository::BASE_IMAGE_URL, "movie" => $movie, "reviews" => $reviews];
    }
}