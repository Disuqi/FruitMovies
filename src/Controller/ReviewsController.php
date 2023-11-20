<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Form\SearchFormType;
use App\Repository\ReviewRepository;
use App\Repository\ReviewVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewsController extends AbstractController
{
    #[Route("reviews", name: "reviews")]
    #[Template("profile/reviews.html.twig")]
    public function reviews() : array
    {
        $reviews = $this->getUser()->getReviews();
        $forms = [];
        foreach($reviews as $review)
        {
            $options =
                [
                    "score" => $review->getScore(),
                    "comment" => $review->getComment(),
                ];

            $forms[$review->getId()] = $this->createForm(ReviewFormType::class, options: $options)->createView();
        }
        $searchForm = $this->createForm(SearchFormType::class);
        return ["reviews" => $reviews, "forms" => $forms, "search_form" => $searchForm];
    }

    #[Route("/addReview/{id}", name: "addReview")]
    public function addReview(Movie $movie, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $review = new Review();
        $review->setMovie($movie);
        $review->setUser($this->getUser());
        return $this->updateReview($review, $request, $entityManager);
    }

    #[Route("/editReview/{id}", name: "editReview")]
    public function editReview(Review $review, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        return $this->updateReview($review, $request, $entityManager);
    }

    #[Route("/deleteReview/{id}", name:"deleteReview")]
    public function deleteReview(Review $review, Request $request, ReviewVoteRepository $reviewVoteRepository, EntityManagerInterface $entityManager) : RedirectResponse
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
        return $this->handleRedirect($movieId, $request);
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
        return $this->handleRedirect($review->getMovie()->getId(), $request);
    }

    private function handleRedirect(int $movieId, Request $request) : RedirectResponse
    {
        $referer = $request->headers->get("referer");
        if($referer)
        {
            $host = parse_url($referer, PHP_URL_HOST);
            $myHost = $request->getHost();
            if($host === $myHost)
            {
                return new RedirectResponse($referer);
            }
        }
        return $this->redirectToRoute("movie", ["id" => $movieId]);
    }
}