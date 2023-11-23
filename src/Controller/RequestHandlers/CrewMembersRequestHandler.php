<?php

namespace App\Controller\RequestHandlers;

use App\Repository\CrewMemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CrewMembersRequestHandler extends AbstractController
{
     #[Route("/crewMembers/{role}/{searchQuery}", name: "crewMembers")]
     public function crewMembers(string $role, string $searchQuery, CrewMemberRepository $crewMemberRepository): Response
     {
         $crewMemberEntities = $crewMemberRepository->findBy(["name" => $searchQuery, "role" => $role]);
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