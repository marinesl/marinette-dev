<?php

namespace App\Security\Voter;

use App\Entity\Media;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MediaVoter extends Voter
{
    public const VIEW = 'MEDIA_VIEW';
    public const INFO = 'MEDIA_INFO';
    public const DELETE = 'MEDIA_DELETE';

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerInterface)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::INFO, self::DELETE])
            && $subject instanceof Media;
    }

    protected function voteOnAttribute(
        string $attribute, 
        mixed $subject, 
        TokenInterface $token
    ): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->authorizationCheckerInterface->isGranted('ROLE_ADMIN')) return true;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;

            case self::INFO:
                // logic to determine if the user can INFO
                // return true or false
                break;

            case self::DELETE:
                // logic to determine if the user can DELETE
                // return true or false
                break;
        }

        return false;
    }
}
