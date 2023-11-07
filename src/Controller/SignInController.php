<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SignInController extends AbstractController
{
    #[Route('/signIn', name: 'signIn')]
    #[Template('authentication/signIn.html.twig')]
    public function index(AuthenticationUtils $authenticationUtils): array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return [ 'last_username' => $lastUsername,
            'error' => $error,];
    }
}
