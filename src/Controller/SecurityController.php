<?php

/**
 * Controller permettant l'authentification des utilisateurs.
 *
 * Méthodes :
 * - login() : Connexion de l'utilisateur
 * - logout() : Déconnexion de l'utilisateur
 * - forgottenPassword() : Demande de mot de passe oublié
 * - resetPassword() : Réinitialisation du mot de passe
 */

declare(strict_types=1);

namespace App\Controller;

use App\Form\Security\ForgottenPasswordType;
use App\Form\Security\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/manager', name: 'app_')]
class SecurityController extends AbstractController
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly SecurityService $securityService
    ) {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Connexion de l'utilisateur.
     *
     * @param AuthenticationUtils authenticationUtils
     *
     * @return Response security/login.twig
     */
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('back_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Demande de mot de passe oublié.
     *
     * @param TokenGeneratorInterface tokenGeneratorInterface
     * @param MailerService mailerService
     *
     * @return Response security/forgotten_password.twig
     */
    #[Route('/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword(
        TokenGeneratorInterface $tokenGeneratorInterface,
        MailerService $mailerService
    ): Response {
        // On récupère le formulaire de mot de passe oublié
        $form = $this->createForm(ForgottenPasswordType::class);

        // On gère la requête
        $form->handleRequest($this->request);

        // Si le formulaire a été envoyé et s'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On va chercher l'utilisateur par son email
            $user = $this->userRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if ($user) {
                // On génère un token de réinitialisation
                $token = $this->securityService->setToken($user);

                // On génère un lien de réinitialisation
                $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail avec le service
                // TODO: renvoyer un boolean pour gérer les messages flash ici et non dans le service
                $mailerService->sendEmail(
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe',
                    'forgotten_password',
                    $context
                );

                $this->addFlash('success', "L'e-mail a été envoyé avec succès.");

                return $this->redirectToRoute('app_login');
            }

            // Si on n'a pas d'utilisateur
            $this->addFlash('danger', 'Un problème est survenu.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgotten_password.twig', compact('form'));
    }

    /**
     * Réinitialisation du mot de passe.
     *
     * @param string token
     * @param UserPasswordHasherInterface passwordHasher
     *
     * @return Response security/reset_password.twig
     */
    #[Route('/mot-de-passe-oublie/{token}', name: 'reset_password')]
    public function resetPassword(string $token): Response
    {
        // On vérifie si on a ce token dans la base
        $user = $this->userRepository->findOneBy(['token_reset_password' => $token]);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $this->securityService->deleteToken($user, $form->get('password')->getData());

                $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.twig', compact('form'));
        }

        $this->addFlash('danger', 'Le jeton est invalide.');

        return $this->redirectToRoute('app_login');
    }
}
