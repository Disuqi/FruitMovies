<?php

namespace App\Entity;

use App\Repository\CrewMemberRepository;
use App\Utils\Constants\PhotoSize;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   description="CrewMember model",
 *   type="object",
 *   title="CrewMember model"
 * )
 */
#[ORM\Entity(repositoryClass: CrewMemberRepository::class)]
class CrewMember
{
    /**
     * @OA\Property(description="ID of the crew member", format="int64", example=1)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @OA\Property(description="Name of the crew member", type="string", example="John Doe")
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @OA\Property(description="Photo of the crew member", type="string", example="path/to/image.jpg")
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /**
     * @OA\Property(description="Role of the crew member", type="string", example="Director")
     */
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
