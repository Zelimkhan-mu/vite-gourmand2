<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@viteetgourmand.fr', 'Site Web Vite & Gourmand'))
                ->to(new Address('contact@viteetgourmand.fr', 'Admin Vite & Gourmand'))
                ->replyTo($data['email'])
                ->subject('Nouveau message de contact : ' . $data['subject'])
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'from_email' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                ]);

            try {
                $mailer->send($email);
                return $this->redirectToRoute('app_contact_success');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message : ' . $e->getMessage());
            }
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/contact/confirmation', name: 'app_contact_success')]
    public function success(): Response
    {
        return $this->render('contact/success.html.twig');
    }
}
