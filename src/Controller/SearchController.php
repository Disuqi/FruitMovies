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

class SearchController extends AbstractController
{
    #[Route('/search', name:"search")]
    #[Template('search.html.twig')]
    public function home(MovieRepository $movieRepository) : array
    {
        $juiciestPicks = $movieRepository->getTopRatedThisWeek();
        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "movies" => $juiciestPicks];
    }
}