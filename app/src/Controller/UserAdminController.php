<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\MailerService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class UserAdminController extends AbstractController
{
    #[Route('/utilisateurs', name: 'admin_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function usersList(UserRepository $userRepo): Response
    {
        $allUsers = $userRepo->findAll();
        $users = array_filter($allUsers, function (User $user) {
            return in_array('ROLE_EMPLOYE', $user->getRoles());
        });

        return $this->render('dashboard_admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/utilisateurs/creer', name: 'admin_users_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userCreate(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerService $mailerService
    ): Response {
        if (!$this->isCsrfTokenValid('user_create', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');

        if (!$email || !$plainPassword || !$firstName || !$lastName) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('admin_users_list');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_EMPLOYE']);

        $user->setPhone('0000000000');
        $user->setAdresse('Adresse à compléter');
        $user->setVille('Ville');
        $user->setCodePostal('00000');

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        try {
            $mailerService->sendBienvenueEmploye($user);

            $this->addFlash('success', 'Compte employé créé et email de bienvenue envoyé.');
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Compte créé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/utilisateurs/{id}/toggle', name: 'admin_users_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userToggle(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('user_toggle_' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $isActive = $user->isActive() ?? true;
        $user->setActive(!$isActive);

        $em->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Le compte a été $status.");

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/utilisateurs/{id}/supprimer', name: 'admin_users_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userDelete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('user_delete_' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Compte employé supprimé.');
        return $this->redirectToRoute('admin_users_list');
    }

}