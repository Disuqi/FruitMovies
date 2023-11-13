<?php
namespace App\Repository;

use App\Entity\MovieCrewMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MovieCrewMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovieCrewMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovieCrewMember[]    findAll()
 * @method MovieCrewMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieCrewMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieCrewMember::class);
    }
}
