<?php

/**
 * Service de gestion de la sécurité
 * 
 * Méthodes :
 * - setToken() : On génère un token de réinitialisation et on l'applique à l'utilisarteur
 */

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityService
{
    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly Request $request,
        private readonly EntityManagerInterface $em,
        private readonly TokenGeneratorInterface $tokenGeneratorInterface,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * On génère un token de réinitialisation et on l'applique à l'utilisarteur
     * 
     * @param User user
     * 
     * @return string token
     */
    public function setToken($user): string
    {
        // On génère un token de réinitialisation
        $token = $this->tokenGeneratorInterface->generateToken();
        $user->setTokenResetPassword($token);

        $this->em->persist($user);
        $this->em->flush();

        return $token;
    }


    /**
     * On efface le token
     * 
     * @param User user
     * @param string password
     */
    public function deleteToken(
        $user,
        $password
    )
    {
        $user->setTokenResetPassword(null);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $password
            )
        );

        $this->em->persist($user);
        $this->em->flush();
    }
}