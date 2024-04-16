<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   description="Review model",
 *   type="object",
 *   title="Review model"
 * )
 */
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\UniqueConstraint(name: "movie_user_unique", columns: ["movie_id", "user_id"])]
class Review
{
    /**
     * @OA\Property(description="ID of the review", format="int64", example=1)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @OA\Property(description="Movie that the review is for", ref="#/components/schemas/Movie")
     */
    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Movie $movie = null;

    /**
     * @OA\Property(description="User that wrote the review", ref="#/components/schemas/User")
     */
    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    /**
     * @OA\Property(description="Score of the review", type="integer", example=5)
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $score = null;

    /**
     * @OA\Property(description="Comment of the review", type="string", example="This movie was amazing!")
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    /**
     * @OA\Property(description="Date the review was written", type="string", example="2022-01-01T00:00:00+00:00")
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $date_reviewed = null;

    #[Serializer\Exclude]
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
