<?php

namespace App\Controller\RequestHandlers;

use App\Repository\CrewMemberRepository;
use App\Utils\Constants\CrewMemberRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrewMembersRequestHandler extends AbstractController
{
     #[Route("/directors", name: "directors")]
     public function directors(Request $request, CrewMemberRepository $crewMemberRepository): Response
     {
         return $this->findCrewMembers(CrewMemberRole::Director, $request, $crewMemberRepository);
     }

    #[Route("/actors", name: "actors")]
    public function actors(Request $request, CrewMemberRepository $crewMemberRepository): Response
    {
        return $this->findCrewMembers(CrewMemberRole::Actor, $request, $crewMemberRepository);
    }

    private function findCrewMembers(CrewMemberRole $role, Request $request, CrewMemberRepository $crewMemberRepository) : Response
    {
        $searchQuery = $request->request->get("searchQuery");
        $crewMemberEntities = $crewMemberRepository->findCrewMember($role, $searchQuery);
        $crewMembers = [];
        foreach ($crewMemberEntities as $crewMemberEntity)
        {
            $crewMembers[] =
                [
                    "id" => $crewMemberEntity->getId(),
                    "name" => $crewMemberEntity->getName()
                ];
        }
        return new Response(json_encode($crewMembers));
    }
}
