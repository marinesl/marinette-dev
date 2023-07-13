<?php

/**
 * Service de gestion des utilisateurs
 * 
 * Méthodes :
 * - edit() : Modification d'un utilisateur
 * - editPassword() : Modification du mot de passe
 */

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private $request;
    
    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * Modification d'un utilisateur
     * 
     * @param User user
     * 
     * @return JsonResponse success
     */
    public function edit($user): JsonResponse
    {
        // Date de la modification
        $user->setEditedAt(new \DateTimeImmutable());

        // On registre les informations de l'utilisateur
        $this->em->persist($user);
        $this->em->flush();

        return $this->json([ 'status' => 'success', 'message' => 'Les informations ont été enregistrées.' ]);
    }


    /**
     * Modification du mot de passe
     * 
     * @param User user
     * @param string currentPassword
     * 
     * @return JsonResponse success
     */
    public function editPassword(
        $user,
        $currentPassword,
        $newPassword
    ): JsonResponse
    {
        // On vérifie si le mot de passe actuel rentré est identique au mot de passe de l'utilisateur connecté
        if (!$this->hasher->isPasswordValid($user, $currentPassword))
            return $this->json([ 'status' => 'danger', 'message' => "Votre mot de passe actuel est incorrect."]);

        // Encode the plain password
        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $newPassword
            )
        );

        // Date de la modification
        $user->setEditedAt(new \DateTimeImmutable());

        // On registre les informations de l'utilisateur
        $this->em->persist($user);
        $this->em->flush();

        return $this->json([ 'status' => 'success', 'message' => "Le nouveau mot de passe a été enregistré."]);
    }
}