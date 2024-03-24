<?php

namespace App\Controller\Api;

use App\Entity\Review;
use App\Entity\ReviewVote;
use App\Form\ReviewApiFormType;
use App\Form\ReviewVoteApiFormType;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewVoteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewsAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private ReviewRepository $reviewRepository;

    public function __construct(EntityManagerInterface $entityManager, ReviewRepository $reviewRepository)
    {
        $this->entityManager = $entityManager;
        $this->reviewRepository = $reviewRepository;
    }

    #[Rest\Get("api/v1/reviews", name: "getReviews")]
    public function getReviews(Request $request): View
    {
        $contents = json_decode($request->getContent());
        $page = 1;
        if(isset($contents->page))
            $page = $contents->page;

        $totalPages = $this->reviewRepository->getTotalPages();
        if($page > $totalPages)
            return View::create("Out of range", Response::HTTP_NOT_FOUND);

        if(!$page || $page == "")
            $page = 1;
        $searchResult = $this->reviewRepository->getReviewsPage($page);
        return View::create($searchResult, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/reviews/{id}", name: "getReview")]
    public function getReview(int $id): View
    {
        $review = $this->reviewRepository->find($id);

        if(!$review)
            return View::create("Review not found", Response::HTTP_NOT_FOUND);

        return View::create($review, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/reviews/{id}/votes", name: "getReviewVotes")]
    public function getReviewVotes(int $id): View
    {
        $review = $this->reviewRepository->find($id);

        if(!$review)
            return View::create("Review not found", Response::HTTP_NOT_FOUND);

        $votes = $review->getReviewVotes();

        return View::create($votes, Response::HTTP_OK);
    }

    #[Rest\Post("api/v1/reviews", name: "postReview")]
    public function postReview(Request $request, UserRepository $userRepository, MovieRepository $movieRepository): View
    {
        $review = new Review();
        $form = $this->createForm( ReviewApiFormType::class, $review);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $userRepository->find($data["user_id"]);
            if(!$user)
                return View::create("User not found", Response::HTTP_NOT_FOUND);
            $review->setUser($user);

            $movie = $movieRepository->find($data["movie_id"]);
            if(!$movie)
                return View::create("Movie not found", Response::HTTP_NOT_FOUND);
            $review->setMovie($movie);

            $existingReview = $this->reviewRepository->findOneBy(["user" => $user, "movie" => $movie]);
            if($existingReview)
                return View::create("Review already exists", Response::HTTP_ALREADY_REPORTED)->setLocation("/api/v1/reviews/" . $existingReview->getId());

            $review->setDateReviewed(new \DateTimeImmutable());
            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return View::create("Successfully added review",  Response::HTTP_CREATED)->setLocation("/api/v1/reviews/" . $review->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_NOT_FOUND);
    }

    #[Rest\Put("api/v1/reviews/{id}", name: "putReview")]
    public function putReview(Request $request, int $id, UserRepository $userRepository, MovieRepository $movieRepository): View
    {
        $review = $this->reviewRepository->find($id);
        if(!$review)
            return View::create("Review not found", Response::HTTP_NOT_FOUND);

        $form = $this->createForm( ReviewApiFormType::class, $review);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $userRepository->find($data["user_id"]);
            if(!$user)
                return View::create("User not found", Response::HTTP_NOT_FOUND);
            $review->setUser($user);

            $movie = $movieRepository->find($data["movie_id"]);
            if(!$movie)
                return View::create("Movie not found", Response::HTTP_NOT_FOUND);
            $review->setMovie($movie);

            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return View::create("Successfully updated review",  Response::HTTP_OK);
        }
        return View::create($form->getErrors(), Response::HTTP_NOT_FOUND);
    }

    #[Rest\Delete("api/v1/reviews/{id} ", name: "deleteReview")]
    public function deleteReview(int $id) : View
    {
        $review = $this->reviewRepository->find($id);
        if(!$review)
            return View::create("Review not found", Response::HTTP_NOT_FOUND);

        $this->entityManager->remove($review);
        $this->entityManager->flush();

        return View::create("Successfully deleted review", Response::HTTP_OK);
    }
//
//    #[Rest\Post("api/v1/reviews/{reviewId}/votes", name: "postReviewVote")]
//    public function postReviewVote(int $reviewId, Request $request, UserRepository $userRepository): View
//    {
//        $review = $this->reviewRepository->find($reviewId);
//        if(!$review)
//            return View::create("Review not found", Response::HTTP_NOT_FOUND);
//
//        $reviewVote = new ReviewVote();
//        $reviewVote->setReview($review);
//
//        $form = $this->createForm( ReviewVoteApiFormType::class, $reviewVote);
//        $data = json_decode($request->getContent(), true);
//        $form->submit($data);
//
//        if($form->isSubmitted() && $form->isValid())
//        {
//            $user = $userRepository->find($data["user_id"]);
//            if(!$user)
//                return View::create("User not found", Response::HTTP_NOT_FOUND);
//            $reviewVote->setUser($user);
//
//            $existingVotes = $review->getReviewVotes();
//            foreach($existingVotes as $vote)
//            {
//                if ($vote->getUser() == $user)
//                    return View::create("Vote already exists", Response::HTTP_ALREADY_REPORTED);
//            }
//
//            $this->entityManager->persist($reviewVote);
//            $this->entityManager->flush();
//
//            return View::create("Successfully added review vote", Response::HTTP_CREATED)->setLocation("/api/v1/reviews/" . $review->getId() . "/votes");
//        }
//        return View::create($form->getErrors(), Response::HTTP_NOT_FOUND);
//    }
}