<?php

namespace App\Controller\Api;

use App\Entity\Movie;
use App\Entity\MovieCrewMember;
use App\Form\MovieApiFormType;
use App\Repository\CrewMemberRepository;
use App\Repository\MovieCrewMemberRepository;
use App\Repository\MovieRepository;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\KernelInterface;

class MoviesAPI extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;

    public function __construct(EntityManagerInterface $entityManager, MovieRepository $movieRepository, CrewMemberRepository $crewMemberRepository, MovieCrewMemberRepository $movieCrewMemberRepository)
    {
        $this->entityManager = $entityManager;
        $this->movieRepository = $movieRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/movies",
     *     summary="Get a page of movies",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page of movies retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="results", 
     *                 type="array", 
     *                 @OA\Items(ref="#/components/schemas/Movie")
     *             ),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="total_pages", type="integer")
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Page not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/movies", name: "getMovies")]
    public function getMovies(Request $request): View
    {
        $contents = json_decode($request->getContent());

        $page = 1;
        if(isset($contents->page) && is_int($contents->page))
            $page = $contents->page;
        if($request->query->getInt("page"))
            $page = $request->query->getInt("page");

        $totalPages = $this->movieRepository->getTotalPages();
        if($page > $totalPages)
            return View::create(["message" => "Out of range"], Response::HTTP_NOT_FOUND);

        if(!$page || $page == "")
            $page = 1;

        $searchResult = $this->movieRepository->getPage($page);
        return View::create($searchResult, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/movies/{id}",
     *     summary="Get a specific movie by ID",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the movie",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Movie retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Movie")
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/movies/{id}", name: "getMovie")]
    public function getMovie(int $id): View
    {
        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        return View::create($movie, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/movies",
     *     summary="Create a new movie",
     *     tags={"Movies"},
     *     @OA\RequestBody(
     *         description="Movie data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/NewMovie")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Movie created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully added Movie")
     *         ),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the new movie",
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only admins can create movies",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can create movies")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Movie already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie already exists")
     *         )
     *     )
     * )
     */
    #[Rest\Post("api/v1/movies", name: "postMovie")]
    public function postMovie(Request $request): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can create movies"], Response::HTTP_FORBIDDEN);

        $movie = new Movie();
        $form = $this->createForm( MovieApiFormType::class, $movie);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $existingMovie = $this->movieRepository->findOneBy(["title" => $movie->getTitle(), "overview" => $movie->getOverview()]);
            if($existingMovie)
                return View::create(["message" => "Movie already exists"], Response::HTTP_CONFLICT)->setLocation("/api/v1/movies/" . $existingMovie->getId());

            try
            {
                $date = new \DateTimeImmutable($data["release_date"]);
            }
            catch(Exception $e)
            {
                return View::create(["message" => "Invalid date. It should be in this format DD-MM-YYYY"], Response::HTTP_BAD_REQUEST);
            }
            $movie->setReleaseDate($date);
            $this->entityManager->persist($movie);
            $this->entityManager->flush();

            return View::create(["message" => "Successfully added Movie"],  Response::HTTP_CREATED)->setLocation("/api/v1/movies/" . $movie->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/movies/{id}",
     *     summary="Update an existing movie",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the movie to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Movie data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Movie")
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Movie updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated Movie")
     *         ),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the updated movie",
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only admins can modify movies",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can modify movies")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie not found")
     *         )
     *     )
     * )
     */
    #[Rest\Put("api/v1/movies/{id}", name: "putMovie")]
    public function putMovie(Request $request, int $id) : View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can modify movies"], Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        $form = $this->createForm(MovieApiFormType::class, $movie);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->entityManager->persist($movie);
            $this->entityManager->flush();
            return View::create(["message" => "Successfully updated Movie"], Response::HTTP_ACCEPTED)->setLocation("/api/v1/movies/" . $movie->getId());
        }
        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }



    /*
    * @OA\Post(
    *     path="/api/v1/movies/{id}/image",
    *     summary="Upload an image for a movie",
    *     tags={"Movies"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="The ID of the movie",
    *         required=true,
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\RequestBody(
    *         description="Image data",
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="cover_photo",
    *                     type="string",
    *                     format="binary"
    *                 )
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="Image uploaded successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="Successfully saved movie image")
    *         ),
    *         @OA\Header(
    *             header="Location",
    *             description="URL of the updated movie",
    *             @OA\Schema(type="string")
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Invalid image file",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="Invalid image file. You can only upload .png, .jpeg or .jpg image files")
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
    *         description="Only admins can modify movies",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="Only admins can modify movies")
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Movie not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="Movie not found")
    *         )
    *     )
    * )
    */
    #[Rest\Post("api/v1/movies/{id}/image", name:"postMovieImage")]
    public function postMovieImage(Request $request, ImageSaver $imageSaver, int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can modify movies"], Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        $cover_photo = $request->files->get("cover_photo");
        if(!$cover_photo)
            return View::create(["message" => "No image uploaded or incorrectly labelled"], Response::HTTP_BAD_REQUEST);

        try
        {
            $imageSaver->saveImage($movie, $cover_photo);
        }
        catch(\Error|Exception $e)
        {
            return View::create(["message" => "Invalid image file. You can only upload .png, .jpeg or .jpg image files"],Response::HTTP_BAD_REQUEST);
        }

        return View::create(["message" => "Successfully saved movie image"], Response::HTTP_ACCEPTED)->setLocation("/api/v1/movies/" . $movie->getId());
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/movies/{id}",
     *     summary="Delete a movie",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the movie to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Movie deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted movie")
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only admins can delete movies",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can delete movies")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie not found")
     *         )
     *     )
     * )
     */
    #[Rest\Delete("api/v1/movies/{id}", name: "deleteMovie")]
    public function deleteMovie(int $id) : View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can delete movies"], Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        $this->entityManager->remove($movie);
        $this->entityManager->flush();

        return View::create(["message" => "Successfully deleted movie"], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/movies/{id}/crewMembers",
     *     summary="Get the crew members of a movie",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the movie",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Crew members retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="director", type="string"),
     *             @OA\Property(property="actors", type="array", @OA\Items(type="string"))
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie not found")
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/movies/{id}/crewMembers", name: "getMovieCrewMembers")]
    public function getMovieCrewMembers(int $id): View
    {
        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        return View::create(["director" => $movie->getDirector(), "actors" => $movie->getActors()], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/movies/{id}/crewMembers",
     *     summary="Update the crew members of a specific movie",
     *     tags={"Movies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the movie",
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
     *         description="Crew members updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CrewMember"),
     *         @OA\Header(
     *             header="Location",
     *             description="URL of the updated movie",
     *             @OA\Schema(type="string")
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
     *         response=401,
     *         description="Expired Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="int", example="401"),
     *             @OA\Property(property="message", type="string", example="Expired JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movie or crew member not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movie or crew member not found")
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
     *         response=403,
     *         description="Only admins can edit movies",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only admins can edit movies")
     *         )
     *     )
     * )
     */
    #[Rest\Put("api/v1/movies/{id}/crewMembers", name:"putMovieCrewMembers")]
    public function putMovieCrewMembers(Request $request, CrewMemberRepository $crewMemberRepository, MovieCrewMemberRepository $movieCrewMemberRepository, int $id): View
    {
        if(!$this->isGranted("ROLE_ADMIN"))
            return View::create(["message" => "Only admins can edit movies"], Response::HTTP_FORBIDDEN);

        $movie = $this->movieRepository->find($id);
        if(!$movie)
            return View::create(["message" => "Movie not found"], Response::HTTP_NOT_FOUND);

        $data = json_decode($request->getContent());
        $existingRelations = $movieCrewMemberRepository->findBy(["movie" => $movie->getId()]);
        $newRelations = [];

        if(!empty($data->director))
        {
            if(is_array($data->director))
                return View::create(["message" => "A movie can only have one director"], Response::HTTP_BAD_REQUEST);

            if(empty($data->director->id))
                return View::create(["message" => "Missing Director id. Only existing directors can be added"], Response::HTTP_BAD_REQUEST);

            $director = $crewMemberRepository->find($data->director->id);
            if(!$director)
                return View::create(["message" => "Director not found"], Response::HTTP_NOT_FOUND);

            $movieDirectorRelation = new MovieCrewMember();
            $movieDirectorRelation->setMovie($movie);
            $movieDirectorRelation->setCrewMember($director);

            $newRelations[] = $movieDirectorRelation;
        }

        if(!empty($data->actors))
        {
            if(!is_array($data->actors))
                $data->actors = [$data->actors];

            foreach($data->actors as $actorData)
            {
                if(empty($actorData->id))
                    return View::create(["message" => "One or more actors did not have an id. Only existing actors can be added"], Response::HTTP_BAD_REQUEST);

                $actor = $crewMemberRepository->find($actorData->id);
                if(!$actor)
                    return View::create(["message" => "Actor not found. ID: " . $actorData->id], Response::HTTP_NOT_FOUND);

                $movieActorRelation = new MovieCrewMember();
                $movieActorRelation->setMovie($movie);
                $movieActorRelation->setCrewMember($actor);

                $newRelations[] = $movieActorRelation;
            }
        }

        foreach ($existingRelations as $existingRelation)
        {
            $this->entityManager->remove($existingRelation);
        }
        $this->entityManager->flush();

        foreach ($newRelations as $newRelation)
        {
            $this->entityManager->persist($newRelation);
        }
        $this->entityManager->flush();

        return View::create(["message" => "Crew members updated successfully"], Response::HTTP_ACCEPTED)->setLocation("api/v1/movie/" . $movie->getId() . "/crewMembers");
    }
}