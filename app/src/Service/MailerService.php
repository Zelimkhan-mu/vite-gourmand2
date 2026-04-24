<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendBienvenueClient(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@vite-et-gourmand.fr', 'Vite & Gourmand'))
            ->to($user->getEmail())
            ->subject('Bienvenue chez Vite & Gourmand !')
            ->htmlTemplate('emails/mail_bienvenue.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendOrderConfirmation(Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@vite-et-gourmand.fr', 'Vite & Gourmand'))
            ->to($commande->getUser()->getEmail())
            ->subject('Confirmation de votre commande')
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context(['commande' => $commande]);

        $this->mailer->send($email);
    }

    public function sendRetourMateriel(Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@viteetgourmand.fr', 'Vite & Gourmand'))
            ->to($commande->getUser()->getEmail())
            ->subject('Important : Matériel à restituer')
            ->htmlTemplate('emails/attente_retour_materiel.html.twig')
            ->context(['commande' => $commande]);

        $this->mailer->send($email);
    }

    public function sendCommandeTerminee(Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@viteetgourmand.fr', 'Vite & Gourmand'))
            ->to($commande->getUser()->getEmail())
            ->subject('Votre commande est terminée - Donnez votre avis !')
            ->htmlTemplate('emails/commande_terminee.html.twig')
            ->context(['commande' => $commande]);

        $this->mailer->send($email);
    }

    public function sendBienvenueEmploye(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@viteetgourmand.fr', 'Vite & Gourmand Admin'))
            ->to($user->getEmail())
            ->subject('Bienvenue chez Vite & Gourmand - Création de compte')
            ->htmlTemplate('emails/welcome_employee.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}
