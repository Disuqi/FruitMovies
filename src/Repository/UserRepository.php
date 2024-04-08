<?php

namespace App\Repository;

use App\Entity\User;
use App\Utils\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends PaginatedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByUsername(string $username, int $page = 0) : SearchResult
    {
        $qb = $this->createQueryBuilder("u")
            ->where("u.username LIKE :username")
            ->setParameter("username", "%".$username."%");

        $totalPages = $this->getTotalPages();

        if($page > 0)
        {
            $qb->setFirstResult(($page - 1) * PAGE_SIZE)
                ->setMaxResults(PAGE_SIZE);
        }
        $results =  $qb->getQuery()->getResult();
        return new SearchResult($results, $page, $totalPages);
    }
}
