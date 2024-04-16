<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;


class UsersAPI extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="results", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="total_pages", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Out of range",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Out of range")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Find user by ID",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved user",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/users/{id}", name: "findUserById")]
    public function findUserById(int $id): View
    {
        $user = $this->userRepository->find($id);
        if(!$user)
            return View::create("User not found", Response::HTTP_NOT_FOUND);

        return View::create($user, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}/reviews",
     *     summary="Get user's reviews",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved user's reviews",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/users/{id}/reviews", name: "getUsersReview")]
    public function getUsersReview(int $id): View
    {
        $user = $this->userRepository->find($id);
        if(!$user)
            return View::create("User not found", Response::HTTP_NOT_FOUND);

        return View::create($user->getReviews(), Response::HTTP_OK);
    }
}