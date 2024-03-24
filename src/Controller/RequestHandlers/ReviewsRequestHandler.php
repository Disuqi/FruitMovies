<?php

namespace App\Controller\RequestHandlers;

use App\Entity\Movie;
use App\Entity\Review;
use App\Entity\ReviewVote;
use App\Form\ReviewFormType;
use App\Repository\ReviewVoteRepository;
use App\Utils\Errors\ErrorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReviewsRequestHandler extends AbstractController
{
    #[Route("/addReview/{id}", name: "addReview")]
    public function addReview(Movie $movie, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if($this->getUser()->isRestricted())
        {
            return $this->handleRedirect($request);
        }

        $review = new Review();
        $review->setMovie($movie);
        $review->setUser($this->getUser());
        return $this->updateReview($review, $request, $entityManager);
    }

    #[Route("/editReview/{id}", name: "editReview")]
    public function editReview(Review $review, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if($this->getUser()->isRestricted())
        {
            return $this->handleRedirect($request);
        }
        return $this->updateReview($review, $request, $entityManager);
    }

    #[Route("/removeReview/{id}", name:"removeReview")]
    public function removeReview(Review $review, Request $request, ReviewVoteRepository $reviewVoteRepository, EntityManagerInterface $entityManager) : RedirectResponse
    {
        $movieId = $review->getMovie()->getId();
        if($this->isGranted("ROLE_ADMIN") || $this->getUser() === $review->getUser())
        {
            $votes = $reviewVoteRepository->findBy(["review" => $review->getId()]);
            foreach ($votes as $vote)
            {
                $entityManager->remove($vote);
            }
            $entityManager->remove($review);
            $entityManager->flush();
        }
        return $this->handleRedirect($request);
    }

    #[Route("/vote/{id}/{liked}", name:"vote")]
    public function vote(Review $review, bool $liked, Request $request, ReviewVoteRepository $reviewVoteRepository, EntityManagerInterface $entityManager) : RedirectResponse
    {
        $reviewVote = $reviewVoteRepository->findOneBy(["user" => $this->getUser()->getId(), "review" => $review->getId()]);

        try
        {
            if(!$reviewVote)
            {
                $reviewVote = new ReviewVote();
                $reviewVote->setReview($review);
                $reviewVote->setUser($this->getUser());
            }
            else if($reviewVote->isLiked() === $liked)
            {
                $entityManager->remove($reviewVote);
                $entityManager->flush();
                return $this->handleRedirect($request);
            }
            $reviewVote->setLiked($liked);
            $entityManager->persist($reviewVote);
            $entityManager->flush();
        }
        catch(\Exception|\Error $e)
        {
            ErrorHandler::AddError($request->getSession(), "Failed to change vote");
        }
        return $this->handleRedirect($request);
    }

    private function updateReview(Review $review, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $form = $this->createForm(ReviewFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $review->getUser() === $this->getUser()) {
            $review->setDateReviewed(new \DateTimeImmutable());

            try {
                $entityManager->persist($review);
                $entityManager->flush();
            } catch (\Exception $e) {
                print($e->getMessage());
            }
        }
        ErrorHandler::AddFormErrors($request->getSession(), $form);
        return $this->handleRedirect($request);
    }

    private function handleRedirect(Request $request) : RedirectResponse
    {
        $referer = $request->headers->get("referer");
        return new RedirectResponse($referer);
    }
}