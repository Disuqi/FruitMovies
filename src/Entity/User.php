<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Annotations as OA;

enum Roles : string
{
    case USER = 'USER';
    case MODERATOR = 'MODERATOR';
    case ADMIN = 'ADMIN';
}

/**
 * @OA\Schema(
 *   description="User model",
 *   type="object",
 *   title="User model"
 * )
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public string $current_token;

    /**
     * @OA\Property(description="ID of the user", format="int64", example=1)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @OA\Property(description="Username of the user", type="string", example="john_doe")
     */
    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $username;

    #[Serializer\Exclude]
    #[ORM\Column(length: 255, nullable: false)]
    private string $password;

    #[Serializer\Exclude]
    #[ORM\Column(length: 255, nullable: false)]
    private string $email;

    /**
     * @OA\Property(description="Whether the user is restricted", type="boolean", example=false)
     */
    #[ORM\Column(nullable: false)]
    private bool $restricted;

    /**
     * @OA\Property(description="Date the user joined", type="string", example="2022-01-01T00:00:00+00:00")
     */
    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $date_joined;

    /**
     * @OA\Property(description="Profile image of the user", type="string", example="path/to/image.jpg")
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profile_image = null;

    #[Serializer\Exclude]
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $roles = [];

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class)]
    private Collection $reviews;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ReviewVote::class)]
    private Collection $votedReviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->votedReviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isRestricted() : bool
    {
        return $this->restricted;
    }

    public function setRestricted(bool $value) : static
    {
        $this->restricted = $value;

        return $this;
    }

    public function updateRestricted(): void
    {
        $this->restricted = !$this->restricted;
    }

    public function getDateJoined(): ?\DateTimeImmutable
    {
        return $this->date_joined;
    }

    public function setDateJoined(\DateTimeImmutable $date_joined): static
    {
        $this->date_joined = $date_joined;

        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profile_image;
    }

    public function setProfileImage(string $profile_image): static
    {
        $this->profile_image = $profile_image;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getMainRole() : string
    {
        $roles = $this->getRoles();
        if(in_array('ROLE_SUPER_ADMIN', $roles))
        {
            return "Super Admin";
        }
        if(in_array('ROLE_ADMIN', $roles))
        {
            return "Admin";
        }
        return "Normal User";
    }
    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    public function getReviewForMovie(int $movieId) : ?Review
    {
        foreach($this->reviews as $review)
        {
            if($review->getMovie()->getId() === $movieId)
                return $review;
        }
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getVotedReviews(): Collection
    {
        return $this->votedReviews;
    }

    public function getLikedReviews() : Collection
    {
        return $this->votedReviews->filter(function (ReviewVote $reviewVote) {
            return $reviewVote->isLiked();
        });
    }

    public function getDislikedReviews() : Collection
    {
        return $this->votedReviews->filter(function (ReviewVote $reviewVote) {
            return !$reviewVote->isLiked();
        });
    }

    public function getReviewVote(int $reviewId) : ?ReviewVote
    {
        foreach($this->votedReviews as $vote)
        {
            if($vote->getReview()->getId() === $reviewId)
            {
                return $vote;
            }
        }
        return null;
    }

    public function addReviewVote(ReviewVote $reviewVote): static
    {
        if (!$this->votedReviews->contains($reviewVote)) {
            $this->votedReviews->add($reviewVote);
            $reviewVote->setUser($this);
        }

        return $this;
    }

    public function removeReviewVote(ReviewVote $reviewVote): static
    {
        $this->votedReviews->removeElement($reviewVote);
        return $this;
    }
}
