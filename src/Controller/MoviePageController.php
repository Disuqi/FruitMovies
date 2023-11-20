<?php

namespace App\Controller;


use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Form\SearchFormType;
use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviePageController extends AbstractController
{
    #[Route("/movie/{id}", name:"movie")]
    #[Template("movie.html.twig")]
    public function movie(Movie $movie) : array
    {
        $addReviewForm = $this->createForm(ReviewFormType::class);
        $options = [];
        if($this->isGranted("IS_AUTHENTICATED"))
        {
            foreach($movie->getReviews() as $movieReview)
            {
                if($movieReview->getUser() === $this->getUser())
                {
                    $options =
                        [
                            "score" => $movieReview->getScore(),
                            "comment" => $movieReview->getComment(),
                        ];
                    break;
                }
            }
        }
        $editReviewForm = $this->createForm(ReviewFormType::class, options: $options);
        $reviews = $movie->getReviews()->toArray();

        usort($reviews, function ($a, $b) {
            $aValue = $a->getLikesCount() - $a->getDislikesCount();
            $bValue = $b->getLikesCount() - $b->getDislikesCount();
            return $bValue - $aValue;
        });
        $reviews = new ArrayCollection($reviews);
        $searchForm = $this->createForm(SearchFormType::class);
        return ["movie" => $movie, "reviews" => $reviews, "addReviewForm" => $addReviewForm->createView(), "editReviewForm" => $editReviewForm->createView(), "search_form" => $searchForm];
    }
}