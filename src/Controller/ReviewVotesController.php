<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewVote;
use App\Repository\ReviewRepository;
use App\Repository\ReviewVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReviewVotesController extends AbstractController
{
    #[Route("/vote/{id}/{liked}", name:"vote")]
    public function vote(Review $review, bool $liked, ReviewVoteRepository $reviewVoteRepository, EntityManagerInterface $entityManager, LoggerInterface $logger) : RedirectResponse
    {
        $reviewVote = $reviewVoteRepository->findOneBy(["user" => $this->getUser()->getId(), "review" => $review->getId()]);

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
            return $this->redirectToRoute("movie", ["id" => $review->getMovie()->getId()]);
        }
        $reviewVote->setLiked($liked);
        $entityManager->persist($reviewVote);
        $entityManager->flush();
        return $this->redirectToRoute("movie", ["id" => $review->getMovie()->getId()]);
    }
}