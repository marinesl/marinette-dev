<?php

/**
 * Controller permettant l'authentification des utilisateurs.
 *
 * Méthodes :
 * - authenticate() : Authentification de l'utilisateur
 * - onAuthenticationSuccess() : Succès de l'authentification
 * - getLoginUrl() : Récupération de la route de connexion
 */

declare(strict_types=1);

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    public $em;

    public function __construct(private UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Authentification de l'utilisateur.
     *
     * @param Request request
     */
    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    /**
     * Succès de l'authentification.
     *
     * @param Request request
     * @param TokenInterface token
     * @param string firewallName
     *
     * @return Response back_dashboard
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        /* Enregistrement de la date et l'heure de connexion de l'utilisateur */
        // On récupère l'utilisateur
        $user = $token->getUser();
        // On met la date et l'heure courante
        $user->setLastLoggedAt(new \DateTimeImmutable());
        $this->em->persist($user);
        $this->em->flush();

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('back_dashboard'));
    }

    /**
     * Récupération de la route de connexion.
     *
     * @param Request request
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
