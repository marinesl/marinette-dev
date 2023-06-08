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
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manager/user', name: 'back_user_')]
class UserController extends AbstractController
{
    /**
     * Page de modification des informations de l'utilisateur
     * 
     * @param User user
     * @param Request request
     * @param EntityManagerInterface em
     * 
     * @return Response back/user/edit.html.twig
     */
    #[Route('/edit/{username}', name: 'edit')]
    #[Security("is_granted('ROLE_ADMIN') and user === theUser")]
    public function edit(
        User $theUser,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        // On récupère le formulaire de modification des informations de l'utilisateur
        $form = $this->createForm(UserType::class, $theUser);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Date de la modification
            $theUser->setEditedAt(new \DateTimeImmutable());

            // On registre les informations de l'utilisateur
            $em->persist($theUser);
            $em->flush();
            
            $this->addFlash('success', "Les informations ont été enregistrées.");
            return $this->redirectToRoute('back_user_edit', [ 'username' => $this->getUser()->getUsername() ]);
        }

        return $this->render('back/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $theUser
        ]);    
    }


    /**
     * Page de modification du mot de passe de l'utilisateur
     * 
     * @param User user
     * @param Request request
     * @param EntityManagerInterface em
     * @param UserPasswordHasherInterface hasher
     * 
     * @return Response back/user/edit_password.html.twig
     */
    #[Route('/edit/password/{username}', name: 'edit_password')]
    #[Security("is_granted('ROLE_ADMIN') and user === theUser")]
    public function editPassword(
        User $theUser,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        // On récupère le formulaire de modification du mot de passe de l'utilisateur
        $form = $this->createForm(UserPasswordType::class);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // On vérifie si le mot de passe actuel rentré est identique au mot de passe de l'utilisateur connecté
            if (!$hasher->isPasswordValid($theUser, $form->get('current_password')->getData())) {
                $this->addFlash('danger', "Votre mot de passe actuel est incorrect.");
                return $this->redirectToRoute('back_user_edit_password', [ 'username' => $theUser->getUsername() ]);
            }

            // Encode the plain password
            $theUser->setPassword(
                $hasher->hashPassword(
                    $theUser,
                    $form->get('password')->getData()
                )
            );

            // Date de la modification
            $theUser->setEditedAt(new \DateTimeImmutable());

            // On registre les informations de l'utilisateur
            $em->persist($theUser);
            $em->flush();
            
            $this->addFlash('success', "Le nouveau mot de passe a été enregistré.");
            return $this->redirectToRoute('back_user_edit', [ 'username' => $this->getUser()->getUsername() ]);
        }

        return $this->render('back/user/edit_password.html.twig', [
            'form' => $form->createView(),
            'user' => $theUser
        ]);    
    }
}
