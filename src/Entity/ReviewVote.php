<?php

namespace App\Entity;

use App\Repository\ReviewVoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewVoteRepository::class)]
class ReviewVote
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Review::class)]
    #[ORM\JoinColumn(name:"review", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Review $review;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name:"user", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $liked;

    public function getReview(): Review
    {
        return $this->review;
    }

    public function setReview(Review $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function isLiked() : bool
    {
        return $this->liked;
    }

    public function setLiked(bool $liked) : static
    {
        $this->liked = $liked;

        return $this;
    }
}
