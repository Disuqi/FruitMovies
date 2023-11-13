<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $running_time = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_photo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $overview = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $release_date = null;

    #[ORM\OneToMany(mappedBy: 'movie', targetEntity: Review::class)]
    private Collection $reviews;

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

    public function getCoverPhoto(): ?string
    {
        return $this->cover_photo;
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
