<?php

namespace App\Controller\Api;

use App\Entity\Review;
use App\Form\ReviewApiFormType;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;


class ReviewsAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private ReviewRepository $reviewRepository;

    public function __construct(EntityManagerInterface $entityManager, ReviewRepository $reviewRepository)
    {
        $this->entityManager = $entityManager;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews",
     *     summary="Get a page of reviews",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Bearer token needed for this operation"
     *     ),
     *     @OA\RequestBody(
     *         description="Page data",
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="page", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="results", type="array", @OA\Items(ref="#/components/schemas/Review")),
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
    #[Rest\Get("api/v1/reviews", name: "getReviews")]
    public function getReviews(Request $request): View
    {
        $contents = json_decode($request->getContent());
        $page = 1;
        if(isset($contents->page))
            $page = $contents->page;

        $totalPages = $this->reviewRepository->getTotalPages();
        if($page > $totalPages)
            return View::create(["message" => "Out of range"], Response::HTTP_NOT_FOUND);

        if(!$page || $page == "")
            $page = 1;
        $searchResult = $this->reviewRepository->getPage($page);
        return View::create($searchResult, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/{id}",
     *     summary="Get a review by its ID",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Bearer token needed for this operation"
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/reviews/{id}", name: "getReview")]
    public function getReview(int $id): View
    {
        $review = $this->reviewRepository->find($id);

        if(!$review)
            return View::create(["message" => "Review not found"], Response::HTTP_NOT_FOUND);

        return View::create($review, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/{id}/votes",
     *     summary="Get the votes of a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Bearer token needed for this operation"
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Votes retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ReviewVote")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/reviews/{id}/votes", name: "getReviewVotes")]
    public function getReviewVotes(int $id): View
    {
        $review = $this->reviewRepository->find($id);

        if(!$review)
            return View::create(["message" => "Review not found"], Response::HTTP_NOT_FOUND);

        $votes = $review->getReviewVotes();

        return View::create($votes, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reviews",
     *     summary="Create a new review",
     *     tags={"Reviews"},
     *     @OA\RequestBody(
     *         description="Review data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/NewReview")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully added review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully added review")
     *         ),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the new review",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error message", type="string", example="Something is wrong in your request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Review already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review already exists")
     *         )
     *     )
     * )
     */
    #[Rest\Post("api/v1/reviews", name: "postReview")]
    public function postReview(Request $request, UserRepository $userRepository, MovieRepository $movieRepository): View
    {
        $review = new Review();
        $form = $this->createForm( ReviewApiFormType::class, $review);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $this->getUser();
            $review->setUser($user);

            $movie = $movieRepository->find($data["movie_id"]);
            if(!$movie)
                return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);
            $review->setMovie($movie);

            $existingReview = $this->reviewRepository->findOneBy(["user" => $user, "movie" => $movie]);
            if($existingReview)
                return View::create(["message" => "Review already exists"], Response::HTTP_CONFLICT)->setLocation("/api/v1/reviews/" . $existingReview->getId());

            $review->setDateReviewed(new \DateTimeImmutable());
            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return View::create(["message" => "Successfully added review"],  Response::HTTP_CREATED)->setLocation("/api/v1/reviews/" . $review->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/reviews/{id}",
     *     summary="Update a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Review data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/NewReview")
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Successfully updated review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated review")
     *         ),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the updated review",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error message", type="string", example="Something is wrong in your request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot change another user's review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot change another user's review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review or movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review or movie not found")
     *         )
     *     )
     * )
     */
    #[Rest\Put("api/v1/reviews/{id}", name: "putReview")]
    public function putReview(Request $request, int $id, UserRepository $userRepository, MovieRepository $movieRepository): View
    {
        $review = $this->reviewRepository->find($id);
        if(!$review)
            return View::create(["message" => "Review not found"], Response::HTTP_NOT_FOUND);

        $user = $this->getUser();
        if($review->getUser() != $user)
            return View::create(["message" => "Cannot change another user's review"], Response::HTTP_FORBIDDEN);

        $form = $this->createForm( ReviewApiFormType::class, $review);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $movie = $movieRepository->find($data["movie_id"]);
            if(!$movie)
                return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);
            $review->setMovie($movie);

            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return View::create(["message" => "Successfully updated review"],  Response::HTTP_ACCEPTED)->setLocation("/api/v1/reviews/" . $review->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_NOT_FOUND);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/reviews/{id}",
     *     summary="Delete a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete another user's review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete another user's review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review not found")
     *         )
     *     )
     * )
     */
    #[Rest\Delete("api/v1/reviews/{id} ", name: "deleteReview")]
    public function deleteReview(int $id) : View
    {
        $review = $this->reviewRepository->find($id);
        if(!$review)
            return View::create(["message" => "Review not found"], Response::HTTP_NOT_FOUND);

        $user = $this->getUser();
        if($review->getUser() !== $user && !$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Cannot delete another user's review"], Response::HTTP_FORBIDDEN);

        $this->entityManager->remove($review);
        $this->entityManager->flush();

        return View::create(["message" => "Successfully deleted review"], Response::HTTP_OK);
    }
}