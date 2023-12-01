<?php

namespace App\Controller\Pages;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Utils\Errors\ErrorHandler;
use AWD\ImageSaver\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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

            try
            {
                $entityManager->persist($user);
                $entityManager->flush();
            }catch(Exception|Error $e)
            {
                ErrorHandler::AddError("Sign Up Failed");
            }

            $profilePhoto = $form->get("profilePhoto")->getData();

            if($profilePhoto)
            {
                try
                {
                    $imageSaver->saveImage($user, $profilePhoto);
                }catch (Exception|Error $e)
                {
                    ErrorHandler::AddError("Could not save Profile Image");
                }
            }

            try
            {
                $security->login($user);
            }catch (Exception|Error $e)
            {
                ErrorHandler::AddError("Autologin Failed");
            }
            return $this->redirectToRoute("home");
        }

        ErrorHandler::AddFormErrors($form);
        return $this->render("authentication/signUp.html.twig",
            ["form" => $form->createView(), "errors" => ErrorHandler::GetAndClearErrors()]);
    }

    #[Route("/signIn", name: "signIn")]
    #[Template("authentication/signIn.html.twig")]
    public function signIn(AuthenticationUtils $authenticationUtils): array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        ErrorHandler::AddError($error->getMessage());

        $lastUsername = $authenticationUtils->getLastUsername();
        return [ "last_username" => $lastUsername];
    }

    #[Route("/signOut", name: "signOut", methods: ["GET"])]
    public function signOut() : void
    {
    }
}
