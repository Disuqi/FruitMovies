<?php

namespace App\Controller;


use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoviePageController extends AbstractController
{
    #[Route('/movie/{id}', name:"movie")]
    #[Template('movie.html.twig')]
    public function movie(ReviewRepository $reviewRepository, Movie $movie, Request $request, EntityManagerInterface $entityManager) : array
    {

        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $review->setUser($this->getUser());
            $review->setMovie($movie);
            $review->setDateReviewed(new \DateTimeImmutable());

            try
            {
                $entityManager->persist($review);
                $entityManager->flush();
                $reviews[] = $review;
            }catch(\Exception $e)
            {
                print($e->getMessage());
            }
        }
$reviews = $movie->getReviews();
        return ["movie" => $movie, "reviews" => $reviews, "form" => $form->createView()];
    }
}