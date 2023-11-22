<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\UniqueConstraint(name: "movie_user_unique", columns: ["movie_id", "user_id"])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Movie $movie = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $score = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_reviewed = null;

    #[ORM\OneToMany(mappedBy: "review", targetEntity: ReviewVote::class)]
    private Collection $reviewVotes;

    public function __construct()
    {
        $this->reviewVotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDateReviewed(): ?\DateTimeImmutable
    {
        return $this->date_reviewed;
    }

    public function setDateReviewed(\DateTimeImmutable $date_reviewed): static
    {
        $this->date_reviewed = $date_reviewed;

        return $this;
    }

    public function getReviewVotes(): Collection
    {
        return $this->reviewVotes;
    }

    public function getLikesCount() : int
    {
        $likes = $this->reviewVotes->filter(function (ReviewVote $reviewVote) {
            return $reviewVote->isLiked();
        });
        return count($likes);
    }

    public function getDislikesCount() : int
    {
        $dislikes = $this->reviewVotes->filter(function (ReviewVote $reviewVote) {
            return !$reviewVote->isLiked();
        });
        return count($dislikes);
    }

    public function addReviewVote(ReviewVote $reviewVote): static
    {
        if (!$this->reviewVotes->contains($reviewVote)) {
            $this->reviewVotes->add($reviewVote);
            $reviewVote->setReview($this);
        }

        return $this;
    }

    public function removeReviewVote(ReviewVote $reviewVote): static
    {
        if($this->reviewVotes->removeElement($reviewVote))
        {
            $reviewVote->setReview(null);
        }
        return $this;
    }
}
