<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersAPI extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Rest\Get("api/v1/users", name: "getUsers")]
    public function getUsers(Request $request): View
    {
        $contents = json_decode($request->getContent());
        $page = 1;
        if(isset($contents->page))
            $page = $contents->page;

        $totalPages = $this->userRepository->getTotalPages();
        if($page > $totalPages)
            return View::create("Out of range", Response::HTTP_NOT_FOUND);

        if(!$page || $page == "")
            $page = 1;

        $searchResult = $this->userRepository->getPage($page);
        return View::create($searchResult, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/users/{id}", name: "findUserById")]
    public function findUserById(int $id): View
    {
        $user = $this->userRepository->find($id);
        if(!$user)
            return View::create("User not found", Response::HTTP_NOT_FOUND);

        return View::create($user, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/users/{id}/reviews", name: "getUsersReview")]
    public function getUsersReview(int $id): View
    {
        $user = $this->userRepository->find($id);
        if(!$user)
            return View::create("User not found", Response::HTTP_NOT_FOUND);

        return View::create($user->getReviews(), Response::HTTP_OK);
    }
}