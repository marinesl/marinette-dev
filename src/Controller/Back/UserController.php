<?php

/**
 * Controller permettant de gérer les utilisateurs
 * 
 * Méthodes :
 * - edit() : Page de modification des informations de l'utilisateur
 * - editPassword() : Page de modification du mot de passe de l'utilisateur
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\Back\UserPasswordType;
use App\Form\Back\UserType;
use App\Security\Voter\UserVoter;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manager/user', name: 'back_user_')]
class UserController extends AbstractController
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
        private readonly UserService $userService
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Page de modification des informations de l'utilisateur
     * 
     * @param User user
     * 
     * @return Response back/user/edit.html.twig
     */
    #[Route('/edit/{username}', name: 'edit')]
    #[IsGranted(UserVoter::EDIT, subject: 'theUser')]
    public function edit(User $theUser): Response
    {
        // On récupère le formulaire de modification des informations de l'utilisateur
        $form = $this->createForm(UserType::class, $theUser);

        // On gère la requête du formulaire
        $form->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On modifie l'utilisateur
            $userEdited = $this->userService->edit($theUser);
            
            $this->addFlash($userEdited->status, $userEdited->message);

            return $this->redirectToRoute('back_user_edit', [ 'username' => $this->getUser()->getUsername() ]);
        }

        return $this->render('back/user/edit.html.twig', [
            'form' => $form,
            'user' => $theUser
        ]);    
    }


    /**
     * Page de modification du mot de passe de l'utilisateur
     * 
     * @param User user
     * @param UserPasswordHasherInterface hasher
     * 
     * @return Response back/user/edit_password.html.twig
     */
    #[Route('/edit/password/{username}', name: 'edit_password')]
    #[IsGranted(UserVoter::EDIT, subject: 'theUser')]
    public function editPassword(User $theUser): Response
    {
        // On récupère le formulaire de modification du mot de passe de l'utilisateur
        $form = $this->createForm(UserPasswordType::class);

        // On gère la requête du formulaire
        $form->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On modifie le mode de passe
            $passwordEdited = $this->userService->editPassword($theUser, $form->get('current_password')->getData(), $form->get('password')->getData());
            
            $this->addFlash($passwordEdited->status, $passwordEdited->message);

            if ($passwordEdited->status === 'danger') 
                return $this->redirectToRoute('back_user_edit_password', [ 'username' => $user->getUsername() ]);

            if ($passwordEdited->status === 'success') 
                return $this->redirectToRoute('back_user_edit', [ 'username' => $this->getUser()->getUsername() ]);
        }

        return $this->render('back/user/edit_password.html.twig', [
            'form' => $form,
            'user' => $theUser
        ]);    
    }
}
