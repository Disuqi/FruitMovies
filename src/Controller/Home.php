<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Home extends AbstractController
{
    #[Route('/')]
    #[Template('home.html.twig')]
    public function home(MovieRepository $movieRepository) : array
    {
        $movie = $movieRepository->findOneBy(["genre" => "Action"]);
        $juiciestPicks = $movieRepository->findBy(["genre" => "Action"]);

        return ["baseImageUrl"=> MovieRepository::BASE_IMAGE_URL, "pickOfTheWeek" => $movie, "juiciestPicks" => $juiciestPicks];
    }
//    #[Route('/createAnnouncement/{message}')]
//    #[Template('createAnnouncement.html.twig')]
//    public function createAnnouncement(EntityManagerInterface $entityManager, ValidatorInterface $validator, string $message) : array
//    {
//        $announcement = new Announcement($message);
//        $errors = $validator->validate($announcement);
//        if(count($errors) > 0)
//        {
//            throw new \Exception("FAILED - " + $errors[0]);
//        }
//        $entityManager->persist($announcement);
//        $entityManager->flush();
//        return ['announcement' => $announcement];
//    }
//
//    #[Route('/announcements')]
//    #[Template('announcements.html.twig')]
//    public function listAnnouncements(EntityManagerInterface $entityManager) : array
//    {
//        $announcements = $entityManager->getRepository(Announcement::class)->findAll();
//        return ["announcements" => $announcements];
//    }
//
//    #[Route('/find/{message}')]
//    #[Template('announcements.html.twig')]
//    public function findAnnouncement(Announcement $announcement) : array
//    {
//        return ["announcements" => [$announcement]];
//    }
}