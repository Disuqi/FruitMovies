<?php

namespace App\Controller\Pages;

use App\Entity\User;
use App\Form\RegistrationFormType;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class AuthenticationPages extends AbstractController
{
    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @param AuthenticationUtils $authenticationUtils
     * @param Security $security
     * @param ImageSaver $imageSaver
     * @return Response
     */
    #[Route("/signUp", name: "signUp")]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils, Security $security, ImageSaver $imageSaver, LoggerInterface $logger): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = new User();
        $user->setUsername($lastUsername);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get("plainPassword")->getData()
                )
            );
            $user->setDateJoined(new \DateTimeImmutable());
            $user->setRestricted(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $profilePhoto = $form->get("profilePhoto")->getData();

            if($profilePhoto)
            {
                $imageSaver->saveImage($user, $profilePhoto);
                $logger->info("SUCCESSFULLY SAVED IMAGE FOR ID: " . $user->getId());
            }

            $security->login($user);
            return $this->redirectToRoute("home");
        }

        return $this->render("authentication/signUp.html.twig",
            ["form" => $form->createView(),]);
    }

    #[Route("/signIn", name: "signIn")]
    #[Template("authentication/signIn.html.twig")]
    public function signIn(AuthenticationUtils $authenticationUtils): array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return [ "last_username" => $lastUsername,
            "error" => $error,];
    }

    #[Route("/signOut", name: "signOut", methods: ["GET"])]
    public function signOut() : void
    {
    }
}
