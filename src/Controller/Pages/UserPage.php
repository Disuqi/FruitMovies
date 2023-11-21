<?php

namespace App\Controller\Pages;

use App\Entity\User;
use App\Form\ReviewFormType;
use App\Form\SearchFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserPage extends AbstractController
{
    #[Route("/user/{username}", name:"user")]
    #[Template("user.html.twig")]
    public function user(User $user, LoggerInterface $logger) : array
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
                $logger->info("REVIEW: " . $review->getId() . " | SCORE: " . $review->getScore() . " | COMMENT: " . $review->getComment());
                $logger->info("OPTIONS " . " | SCORE: " . $options['score'] . " | COMMENT: " . $options['comment']);

                $editForms[$review->getId()] = $this->createForm(ReviewFormType::class, options: $options)->createView();
            }
        }

        $searchForm = $this->createForm(SearchFormType::class);
        return ["user" => $user, "reviews" => $reviews, "search_form" => $searchForm, "edit_forms" => $editForms];
    }
}