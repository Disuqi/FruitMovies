<?php

namespace App\Controller\RequestHandlers;

use App\Entity\User;
use App\Services\Errors\ErrorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersRequestHandler extends AbstractController
{
    #[Route('makeAdmin/{id}', name: 'makeAdmin')]
    public function makeAdmin(User $user, EntityManagerInterface $entityManager) : RedirectResponse
    {
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('user', ['username' => $user->getUsername() ]);
    }

    #[Route('restrictUser/{id}', name: 'restrictUser')]
    public function restrictUser(User $user, EntityManagerInterface $entityManager) : RedirectResponse
    {
        $user->updateRestricted();
        $user->setRoles([]);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('user', ['username' => $user->getUsername() ]);
    }

    #[Route('deleteUser/{id}', name: 'deleteUser')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, Security $security, Request $request) : RedirectResponse
    {
        if($this->getUser() !== $user && !$this->isGranted('ROLE_SUPER_ADMIN'))
            return $this->redirectToRoute('user', ['id' => $user->getId() ]);

        if($this->getUser() === $user)
        {
            try
            {
                $security->logout(false);
            }
            catch(Exception|Error $e)
            {
                ErrorHandler::AddError($request->getSession(), "Could not Sign Out");
            }
        }

        try
        {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        catch(Exception|Error $e)
        {
            ErrorHandler::AddError($request->getSession(),"Could not delete " . $user->getUsername());
        }

        return $this->redirectToRoute('home');
    }
}