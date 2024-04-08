<?php

namespace App\Controller\Pages;

use App\Form\MovieFormType;
use App\Form\SearchFormType;
use App\Repository\RefreshTokenRepository;
use App\Services\Errors\ErrorHandler;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiSettingsPage extends AbstractController
{
    private RefreshTokenRepository $refreshTokenRepository;

    public function __construct(RefreshTokenRepository $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    #[Route("/apiToken", name: "apiToken")]
    public function apiToken(RefreshTokenGeneratorInterface $generator, RefreshTokenManagerInterface $refreshTokenManager, JWTTokenManagerInterface $tokenManager)
    {
        $tokenString = $tokenManager->create($this->getUser());
        $newToken = $generator->createForUserWithTtl($this->getUser(), 7200);
        $refreshTokenManager->save($newToken);
        return new JsonResponse(["token" => $tokenString, "valid" => $newToken->getValid()->format("d-m-Y H:i:s")]);
    }

    #[Route("/apiSettings", name: "apiSettings")]
    #[Template("apiSettings.html.twig")]
    public function apiSettings(Request $request, TokenStorageInterface $storage, JWTTokenManagerInterface $tokenManager): array
    {
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(MovieFormType::class)->createView();

        $latest_token = $tokenManager->decode($storage->getToken());
        $refresh_token = $this->refreshTokenRepository->findLatestByUsername($this->getUser()->getUsername());

        return [
            "latest_token" => $latest_token,
            "refresh_token" => $refresh_token,
            "search_form" => $searchForm,
            "add_movie_form" => $addMovieForm,
            "errors" => ErrorHandler::GetAndClearErrors($request->getSession())
        ];
    }
}