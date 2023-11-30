<?php

namespace App\Controller\RequestHandlers;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Config\SecurityConfig;

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
    public function deleteUser(User $user, EntityManagerInterface $entityManager, Security $security) : RedirectResponse
    {
        if($this->getUser() !== $user && !$this->isGranted('ROLE_SUPER_ADMIN'))
            return $this->redirectToRoute('user', ['id' => $user->getId() ]);

        if($this->getUser() === $user)
        {
            $security->logout(false);
        }

        $entityManager->remove($user);
        $entityManager->flush();


        return $this->redirectToRoute('home');
    }
}