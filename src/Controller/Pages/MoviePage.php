<?php

namespace App\Controller\Pages;


use App\Entity\Movie;
use App\Form\AddMovieFormType;
use App\Form\ReviewFormType;
use App\Form\SearchFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MoviePage extends AbstractController
{
    #[Route("/movie/{id}", name:"movie")]
    #[Template("movie.html.twig")]
    public function movie(Movie $movie) : array
    {
        $addReviewForm = $this->createForm(ReviewFormType::class)->createView();
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
        $editReviewForm = $this->createForm(ReviewFormType::class, options: $options)->createView();
        $reviews = $movie->getReviews()->toArray();

        usort($reviews, function ($a, $b) {
            $aValue = $a->getLikesCount() - $a->getDislikesCount();
            $bValue = $b->getLikesCount() - $b->getDislikesCount();
            return $bValue - $aValue;
        });
        $reviews = new ArrayCollection($reviews);
        $searchForm = $this->createForm(SearchFormType::class)->createView();
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(AddMovieFormType::class)->createView();
        return [
            "movie" => $movie,
            "reviews" => $reviews,
            "addReviewForm" => $addReviewForm,
            "edit_review_form" => $editReviewForm,
            "search_form" => $searchForm,
            "add_movie_form" => $addMovieForm
            ];
    }
}