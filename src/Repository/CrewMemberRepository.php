<?php

namespace App\Repository;

use App\Entity\CrewMember;
use App\Utils\Constants\CrewMemberRole;
use App\Utils\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CrewMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrewMember::class);
    }

    public function findCrewMember(CrewMemberRole $role, string $searchQuery)
    {
        $qb = $this->createQueryBuilder("m")
                    ->where("m.role = :role")
                    ->andWhere("m.name LIKE :name")
                    ->setParameter("role", $role->value)
                    ->setParameter("name", '%'.$searchQuery.'%');
        return $qb->getQuery()->getResult();
    }

    public function getCrewMembersPage(int $page = 1, CrewMemberRole $role = null) : SearchResult
    {
        $qb = $this->createQueryBuilder("m")
            ->select("m")
            ->setFirstResult(($page - 1) * PAGE_SIZE)
            ->setMaxResults(PAGE_SIZE);

        if($role)
            $qb->where("m.role = :role")->setParameter("role", $role->value);

        $crewMembers = $qb->getQuery()->getResult();

        $totalPages = $this->getTotalPages($role);

        return new SearchResult($crewMembers, $page, $totalPages);
    }

    public function getTotalPages(CrewMemberRole $role = null) : int
    {
        $qb = $this->createQueryBuilder("m")->select("COUNT(DISTINCT m.id)");

        if($role)
            $qb->where("m.role = :role")->setParameter("role", $role->value);

        $totalCount = $qb->getQuery()->getSingleScalarResult();
        return ceil($totalCount/PAGE_SIZE);
    }
}
