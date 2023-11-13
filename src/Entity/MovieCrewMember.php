<?php

namespace App\Entity;

use App\Repository\MovieCrewMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieCrewMemberRepository::class)]
class MovieCrewMember
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn(name:"movie", referencedColumnName: "id", nullable: false)]
    private ?Movie $movie;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CrewMember::class)]
    #[ORM\JoinColumn(name:"crew_member", referencedColumnName: "id", nullable: false)]
    private ?CrewMember $crewMember;

    public function getMovie(): Movie
    {
        return $this->movie;
    }

    public function setMovie(Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getCrewMember(): CrewMember
    {
        return $this->crewMember;
    }

    public function setCrewMember(CrewMember $crewMember): static
    {
        $this->crewMember = $crewMember;

        return $this;
    }
}
