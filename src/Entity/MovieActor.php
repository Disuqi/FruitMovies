<?php

namespace App\Entity;

use App\Repository\MovieActorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieActorRepository::class)]
class MovieActor
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn(name:"movie", referencedColumnName: "id", nullable: false)]
    private ?Movie $movie;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Actor::class)]
    #[ORM\JoinColumn(name:"actor", referencedColumnName: "id", nullable: false)]
    private ?Actor $actor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function setActor(Actor $actor): static
    {
        $this->actor = $actor;

        return $this;
    }
}
