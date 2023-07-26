<?php

namespace App\Security\Voter;

use App\Entity\Page;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PageVoter extends Voter
{
    public const VIEW = 'PAGE_VIEW';
    public const CREATE = 'PAGE_CREATE';
    public const EDIT = 'PAGE_EDIT';
    public const DELETE = 'PAGE_DELETE';
    public const CHANGE_STATUS = 'PAGE_CHANGE_STATUS';

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationCheckerInterface)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::CHANGE_STATUS])
            && $subject instanceof Page;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->authorizationCheckerInterface->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;

            case self::CREATE:
                // logic to determine if the user can CREATE
                // return true or false
                break;

            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;

            case self::DELETE:
                // logic to determine if the user can DELETE
                // return true or false
                break;

            case self::CHANGE_STATUS:
                // logic to determine if the user can CHANGE_STATUS
                // return true or false
                break;
        }

        return false;
    }
}
