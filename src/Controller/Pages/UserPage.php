<?php

namespace App\Controller\Pages;

use App\Entity\User;
use App\Form\MovieFormType;
use App\Form\ReviewFormType;
use App\Form\SearchFormType;
use App\Services\Errors\ErrorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserPage extends AbstractController
{
    #[Route("/user/{username}", name:"user")]
    #[Template("user.html.twig")]
    public function user(User $user, Request $request) : array
    {
        $editForms = [];

        $reviews = $user->getReviews()->toArray();
        usort($reviews, function ($a, $b) {
            $aValue = $a->getLikesCount() - $a->getDislikesCount();
            $bValue = $b->getLikesCount() - $b->getDislikesCount();
            return $bValue - $aValue;
        });
        $reviews = new ArrayCollection($reviews);
        if($this->isGranted("IS_AUTHENTICATED") && $this->getUser() === $user)
        {
            foreach($reviews as $review)
            {
                $options =
                    [
                        "score" => $review->getScore(),
                        "comment" => $review->getComment(),
                    ];

                $editForms[$review->getId()] = $this->createForm(ReviewFormType::class, options: $options)->createView();
            }
        }

        $searchForm = $this->createForm(SearchFormType::class);
        $addMovieForm = null;
        if($this->isGranted("ROLE_ADMIN"))
            $addMovieForm = $this->createForm(MovieFormType::class)->createView();
        return ["user" => $user,
            "reviews" => $reviews,
            "search_form" => $searchForm,
            "edit_forms" => $editForms,
            "add_movie_form" => $addMovieForm,
            "errors" => ErrorHandler::GetAndClearErrors($request->getSession())
        ];
    }
}