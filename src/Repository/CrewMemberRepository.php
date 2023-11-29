<?php

namespace App\Repository;

use App\Entity\CrewMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CrewMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrewMember::class);
    }

    public function findCrewMember(string $role, string $searchQuery)
    {
        $qb = $this->createQueryBuilder("c")
                    ->where("c.role = :role")
                    ->andWhere("c.name LIKE :name")
                    ->setParameter("role", $role)
                    ->setParameter("name", '%'.$searchQuery.'%');
        return $qb->getQuery()->getResult();
    }
}
