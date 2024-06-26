<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use App\Utils\Constants\PhotoSize;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   description="Movie model",
 *   type="object",
 *   title="Movie model"
 * )
 */
#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    /**
     * @OA\Property(description="ID of the movie", format="int64", example=1)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @OA\Property(description="Title of the movie", type="string", example="The Godfather")
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @OA\Property(description="Running time of the movie", type="integer", example=175)
     */
    #[ORM\Column(nullable: true)]
    private ?int $running_time = null;

    /**
     * @OA\Property(description="Cover photo of the movie", type="string", example="path/to/image.jpg")
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_photo = null;

    /**
     * @OA\Property(description="Overview of the movie", type="string", example="The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.")
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $overview = null;

    /**
     * @OA\Property(description="Release date of the movie", type="string", example="1972-03-14T00:00:00+00:00")
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $release_date = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Review::class)]
    private Collection $reviews;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: MovieCrewMember::class)]
    private Collection $crewRelations;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->crewRelations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getRunningTime(): ?int
    {
        return $this->running_time;
    }

    public function setRunningTime(int $running_time): static
    {
        $this->running_time = $running_time;

        return $this;
    }

    public function getImagesDirectoryPath() : string
    {
        return "MovieImages/" . $this->id . "/images/";
    }

    public function getCoverPhoto(PhotoSize $size = PhotoSize::Medium): ?string
    {
        if(!$this->cover_photo) return null;
        if($this->cover_photo[0] !== '/') return '/' . $this->cover_photo;
        return $size->value . $this->cover_photo;
    }

    public function setCoverPhoto(string $cover_photo): static
    {
        $this->cover_photo = $cover_photo;

        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTimeImmutable $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function hasReleased(): bool
    {
        return $this->release_date < new \DateTimeImmutable();
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
            $review->setMovie($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMovie() === $this) {
                $review->setMovie(null);
            }
        }

        return $this;
    }

    public function getDirector() : ?CrewMember
    {
        foreach($this->crewRelations as $relation)
        {
            if($relation->getCrewMember()->getRole() == 'director')
            {
                return $relation->getCrewMember();
            }
        }
        return null;
    }

    public function getActors(): array
    {
        $actors = [];
        foreach($this->crewRelations as $relation)
        {
            if($relation->getCrewMember()->getRole() == 'actor')
                $actors[] = $relation->getCrewMember();
        }
        return $actors;
    }
}
