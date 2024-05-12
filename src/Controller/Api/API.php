<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="Fruit Movies API",
 *   version="1.0.0",
 *   @OA\Contact(
 *     email="support@fruitmovies.com"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="JWT Authentication",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 * 
 * @OA\Post(
 *     path="/api/login_check",
 *     tags={"Authentication"},
 *     description="Login and get JWT token",
 *     @OA\RequestBody(
 *        required=true,
 *        @OA\JsonContent(
 *          @OA\Property(property="username", type="string", example="Username"),
 *          @OA\Property(property="password", type="string", example="?pA$$w0rd!")
 *        )
 *     ),
 *     @OA\Response(
 *      response=200,
 *      description="Successful operation",
 *      @OA\JsonContent(
 *          @OA\Property(property="token", type="string")
 *     ),
 *     @OA\Response(
 *      response=401,
 *      description="Invalid credentials",
 *     @OA\JsonContent(
 *      @OA\Property(property="code", type="int", example="401"),
 *      @OA\Property(property="message", type="string", example="Invalid credentials")
 *      )
 *     )
 *   )
 * )
 *
 * @OA\Post(
 *     path="/api/token/refresh",
 *     tags={"Authentication"},
 *     description="Refresh JWT token",
 *     @OA\Response(
 *      response=200,
 *      description="Successful operation",
 *      @OA\JsonContent(
 *          @OA\Property(property="token", type="string")
 *      )
 *     ),
 *      @OA\Response(
 *          response=401,
 *          description="Missing JWT Refresh Token",
 *          @OA\JsonContent(
 *              @OA\Property(property="code", type="int", example="401"),
 *              @OA\Property(property="message", type="string", example="Missing JWT Refresh Token")
 *          )
 *      )
 * )
 */
class API extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/api/v1/docs",
     *     tags={"Documentation"},
     *     summary="Get API Documentation",
     *     description="Get API Documentation",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="openapi",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="info",
     *                 type="object",
     *                 @OA\Property(
     *                     property="title",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="version",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="contact",
     *                     type="object",
     *                     @OA\Property(
     *                         property="email",
     *                         type="string"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="paths",
     *                 type="object"
     *             )
     *         )
     *     )
     * )
     */
    #[Rest\Get("api/v1/docs", name: "getDocs")]
    public function docs(KernelInterface $kernel): View
    {
        $path = $kernel->getProjectDir() . "/public/openapi.json";
        $data = file_get_contents($path);
        return View::create(json_decode($data, true), Response::HTTP_OK);
    }
}
