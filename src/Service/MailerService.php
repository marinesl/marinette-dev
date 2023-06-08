<?php 

/**
 * Service de gestion des emails.
 * 
 * Méthodes :
 * - sendEmail() : Envoi des emails
 */

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer) 
    {
        $this->mailer = $mailer;
    }


    /**
     * Envoi des emails
     * 
     * @param string to : adresse email de destination
     * @param string subject : objet de l'mail
     * @param string template : nom du fichier Twig du contenu de l'email
     * @param array context : tableau des paramètres à afficher dans le contenu (vue)
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $template,
        array $context
    ): void
    {
        // On va créer le mail
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('Marinette.dev - '.$subject)
            ->htmlTemplate("email/$template.html.twig")
            ->context($context)
        ;

        try {
            // On envoie le mail
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // some error prevented the email sending; display an
            // error message or try to resend the message

            // TODO: voir TODO dans SecurityController
            $this->container->get('request_stack')->getSession()->getFlashBag()->add('danger', "L'email n'a pas été envoyé car : $e.");
        }
    }
}