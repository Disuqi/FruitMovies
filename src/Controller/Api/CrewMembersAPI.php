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
use OpenApi\Annotations as OA;


class CrewMembersAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private CrewMemberRepository $crewMemberRepository;

    public function __construct(EntityManagerInterface $entityManager, CrewMemberRepository $crewMemberRepository)
    {
        $this->entityManager = $entityManager;
        $this->crewMemberRepository = $crewMemberRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/crewMembers",
     *     summary="Get a list of crew members",
     *     tags={"Crew Members"},
     *     security={{"bearerAuth":{"user"}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="The role of the crew member",
     *         required=false,
     *         @OA\Schema(ref="#/components/schemas/CrewMemberRole")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of crew members",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CrewMember")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Out of range",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Out of range")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     )
     * )
     */
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
            return View::create(["message" => "Out of range"], Response::HTTP_NOT_FOUND);

        $searchResult = $this->crewMemberRepository->getCrewMembersPage($page, $role);
        return View::create($searchResult, Response::HTTP_OK);
    }


     /**
     * @OA\Get(
     *     path="/api/v1/crewMembers/{id}",
     *     summary="Get a specific crew member by ID",
     *     tags={"Crew Members"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the crew member",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Crew member details",
     *         @OA\JsonContent(ref="#/components/schemas/CrewMember")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crew member not found",
     *     )
     * )
     */
    #[Rest\Get("api/v1/crewMembers/{id}", name: "getCrewMember")]
    public function getCrewMember(int $id): View
    {
        $crewMember = $this->crewMemberRepository->find($id);
        if(!$crewMember)
            return View::create(["message" => "Crew Member not found" ], Response::HTTP_NOT_FOUND);

        return View::create($crewMember, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/crewMembers/{id}",
     *     summary="Update a specific crew member by ID",
     *     tags={"Crew Members"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the crew member",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Crew member data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CrewMember")
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Crew member updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CrewMember"),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the updated crew member",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crew member not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Crew member not found")
     *         )
     *     )
     * )
     */
    #[Rest\Put("api/v1/crewMembers/{id}", name: "updateCrewMember")]
    public function updateCrewMember(int $id, Request $request): View
    {
        $crewMember = $this->crewMemberRepository->find($id);

        if (!$crewMember) {
            return View::create(["message" => "Crew member not found"], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent());

        if(empty($data->name) || empty($data->role)) {
            return View::create(["message" => "Invalid input"], Response::HTTP_BAD_REQUEST);
        }

        $role = CrewMemberRole::tryFrom($data->role);

        if(empty($role)) {
            return View::create(["message" => "Invalid Role"], Response::HTTP_BAD_REQUEST);
        }

        $crewMember->setRole($role->value);
        $crewMember->setName($data->name);

        $this->entityManager->persist($crewMember);
        $this->entityManager->flush();

        return View::create(["message" => "Successfully updated"], Response::HTTP_ACCEPTED)->setLocation("api/v1/crewMembers/" . $crewMember->getId());
    }


    /**
     * @OA\Post(
     *     path="/api/v1/crewMembers",
     *     summary="Add a new crew member",
     *     tags={"Crew Members"},
     *     @OA\RequestBody(
     *         description="Crew member data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CrewMember")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Crew member created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully created Crew Member")
     *         ),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the new crew memeber",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=208,
     *         description="Crew Member already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Crew Member already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only admins can add crew members",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can add crew members")
     *         )
     *     )
     * )
     */
    #[Rest\Post("api/v1/crewMembers", name:"postCrewMember")]
    public function postCrewMember(Request $request): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can add crew members"], Response::HTTP_FORBIDDEN);

        $data = json_decode($request->getContent());

        if(empty($data->name) || empty($data->role))
            return View::create(["message" => "Invalid body, missing parameters"], Response::HTTP_BAD_REQUEST);

        $crewMember = new CrewMember();
        $role = CrewMemberRole::tryFrom($data->role);

        if(empty($role))
            return View::create(["message" => "Invalid Role"], Response::HTTP_BAD_REQUEST);

        $existingCrewMember = $this->crewMemberRepository->findOneBy(["name" => $data->name, "role" => $role]);
        if($existingCrewMember)
            return View::create(["message" => "Crew Member already exists"], Response::HTTP_ALREADY_REPORTED);

        $crewMember->setRole($role->value);
        $crewMember->setName($data->name);

        $this->entityManager->persist($crewMember);
        $this->entityManager->flush();

        return View::create(["message" => "Successfully created Crew Member"], Response::HTTP_CREATED)->setLocation("api/v1/crewMembers/" . $crewMember->getId());
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/crewMembers/{id}",
     *     summary="Delete a specific crew member",
     *     tags={"Crew Members"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the crew member",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Crew member deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted Crew Member")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Crew member not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Crew member not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only admins can delete crew members",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can delete crew members")
     *         )
     *     )
     * )
     */
    #[Rest\Delete("api/v1/crewMembers/{id}", name: "deleteCrewMember")]
    public function deleteCrewMember(int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can delete crew members"], Response::HTTP_FORBIDDEN);

        $crewMember = $this->crewMemberRepository->find($id);
        if(!$crewMember)
            return View::create(["message" => "Crew Member not found"], Response::HTTP_NOT_FOUND);

        $this->entityManager->remove($crewMember);
        $this->entityManager->flush();

        return View::create(["message" => "Successfully deleted Crew Member"], Response::HTTP_OK);
    }
}