<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class Documentation extends AbstractController
{
    #[Rest\Get("api/v1/docs", name: "getDocs")]
    public function docs(KernelInterface $kernel): View
    {
        $path = $kernel->getProjectDir() . "/public/openapi.json";
        $data = file_get_contents($path);
        return View::create(json_decode($data, true), Response::HTTP_OK);
    }
}