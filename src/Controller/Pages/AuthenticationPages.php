<?php

namespace App\Controller\Pages;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route("/signUp", name: "signUp")]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = new User();
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

            $entityManager->persist($user);
            $entityManager->flush();

            $profilePhoto = $form->get("profilePhoto")->getData();

            if($profilePhoto)
            {
                try
                {
                    $dir = $user->getImagesDirectoryPath();
                    $filename = "profilePhoto." . $profilePhoto->guessExtension();
                    $profilePhoto->move($dir, $filename);
                    $user->setProfileImage($dir . $filename);
                    $entityManager->flush();
                }
                catch(FileException $e)
                {
                    print($e);
                }
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
