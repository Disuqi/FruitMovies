<?php

namespace App\Controller;

use App\Form\SearchFormType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends AbstractController
{
    #[Route('/profile', name: "profile")]
    #[Template('profile/profile.html.twig')]
    public function profile() : array
    {
        $searchForm = $this->createForm(SearchFormType::class);
        return ["search_form"=>$searchForm];
    }
}