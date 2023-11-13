<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use App\Utils\Search\OrderBy;
use App\Utils\Search\SearchOptions;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name:"admin")]
    #[Template('admin.html.twig')]
    public function home(MovieRepository $movieRepository) : array
    {
        return [];
    }
}