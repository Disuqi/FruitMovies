<?php

namespace App\Entity;

use App\Repository\CrewMemberRepository;
use App\Utils\Constants\PhotoSize;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrewMemberRepository::class)]
class CrewMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoto(PhotoSize $size = PhotoSize::Medium): ?string
    {
        if(!$this->photo) return null;
        return $size->value . $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
