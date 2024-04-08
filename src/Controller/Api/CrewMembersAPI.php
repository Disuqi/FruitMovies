<?php

namespace App\Controller\Api;

use App\Entity\CrewMember;
use App\Repository\CrewMemberRepository;
use App\Utils\Constants\CrewMemberRole;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CrewMembersAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private CrewMemberRepository $crewMemberRepository;

    public function __construct(EntityManagerInterface $entityManager, CrewMemberRepository $crewMemberRepository)
    {
        $this->entityManager = $entityManager;
        $this->crewMemberRepository = $crewMemberRepository;
    }

    #[Rest\Get("api/v1/crewMembers", name: "getCrewMembers")]
    public function getCrewMembers(Request $request): View
    {
        $contents = json_decode($request->getContent());
        $page = 1;
        if(!empty($contents->page))
            $page = $contents->page;

        $role = null;
        if(!empty($contents->role))
            $role = CrewMemberRole::tryFrom($contents->role);

        $totalPages = $this->crewMemberRepository->getTotalPages($role);
        if($page > $totalPages)
            return View::create("Out of range", Response::HTTP_NOT_FOUND);

        $searchResult = $this->crewMemberRepository->getCrewMembersPage($page, $role);
        return View::create($searchResult, Response::HTTP_OK);
    }

    #[Rest\Get("api/v1/crewMembers/{id}", name: "getCrewMember")]
    public function getCrewMember(int $id): View
    {
        $crewMember = $this->crewMemberRepository->find($id);
        if(!$crewMember)
            return View::create("Crew Member not found", Response::HTTP_NOT_FOUND);

        return View::create($crewMember, Response::HTTP_OK);
    }

    #[Rest\Post("api/v1/crewMembers", name:"postCrewMember")]
    public function postCrewMember(Request $request): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can add crew members", Response::HTTP_FORBIDDEN);

        $data = json_decode($request->getContent());

        if(empty($data->name) || empty($data->role))
            return View::create("Invalid body, missing parameters", Response::HTTP_BAD_REQUEST);

        $crewMember = new CrewMember();
        $role = CrewMemberRole::tryFrom($data->role);

        if(empty($role))
            return View::create("Invalid Role", Response::HTTP_BAD_REQUEST);

        $existingCrewMember = $this->crewMemberRepository->findOneBy(["name" => $data->name, "role" => $role]);
        if($existingCrewMember)
            return View::create("Crew Member already exists", Response::HTTP_ALREADY_REPORTED);

        $crewMember->setRole($role->value);
        $crewMember->setName($data->name);

        $this->entityManager->persist($crewMember);
        $this->entityManager->flush();

        return View::create("Successfully created Crew Member", Response::HTTP_CREATED)->setLocation("api/v1/crewMembers/" . $crewMember->getId());
    }

    #[Rest\Delete("api/v1/crewMembers/{id}", name:"deleteCrewMember")]
    public function deleteCrewMember(int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create("Only admins can delete crew members", Response::HTTP_FORBIDDEN);

        $crewMember = $this->crewMemberRepository->find($id);
        if(!$crewMember)
            return View::create("Crew Member not found", Response::HTTP_NOT_FOUND);

        $this->entityManager->remove($crewMember);
        $this->entityManager->flush();

        return View::create("Successfully deleted Crew Member", Response::HTTP_OK);
    }
}