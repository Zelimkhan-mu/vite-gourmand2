<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class OrderController extends AbstractController
{
    #[Route('/commande', name: 'app_order', methods: ['GET', 'POST'])]
    public function index(Request $request, MenuRepository $menuRepo): Response
    {
        if ($request->isMethod('POST')) {
            $session = $request->getSession();
            $data = $request->request->all();
            $session->set('order_data', $data);
            return $this->redirectToRoute('app_order_recap');
        }

        $selectedMenu = null;
        $menuId = $request->query->get('menu');
        if ($menuId) {
            $selectedMenu = $menuRepo->find($menuId);
        }

        $menus = $menuRepo->findAll();

        return $this->render('order/index.html.twig', [
            'selectedMenu' => $selectedMenu,
            'menus' => $menus,
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_order_recap', methods: ['GET'])]
    public function recap(Request $request, MenuRepository $menuRepo): Response
    {
        $session = $request->getSession();
        $orderData = $session->get('order_data');

        if (!$orderData) {
            return $this->redirectToRoute('app_order');
        }

        $menu = $menuRepo->find($orderData['menu_id']);
        if (!$menu) {
            return $this->redirectToRoute('app_order');
        }

        $count = (int) $orderData['people_count'];

        $tempCommande = new Commande();
        $tempCommande->setMenu($menu);
        $tempCommande->setNumPersons($count);
        $tempCommande->calculatePricing();

        $total = (float) $tempCommande->getPrixTotal();
        $tva = $total - ($total / 1.2);

        return $this->render('order/recap.html.twig', [
            'order' => $orderData,
            'menu' => $menu,
            'subtotal' => (float) $tempCommande->getPrixMenu(),
            'discountAmount' => (float) $tempCommande->getDiscount(),
            'deliveryFee' => (float) $tempCommande->getFraisLivraison(),
            'tva' => $tva,
            'total' => $total
        ]);
    }

    #[Route('/commande/creer', name: 'app_order_create', methods: ['POST'])]
    public function create(Request $request, MenuRepository $menuRepo, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $session = $request->getSession();
        $orderData = $session->get('order_data');

        if (!$orderData) {
            return $this->redirectToRoute('app_order');
        }

        $menu = $menuRepo->find($orderData['menu_id']);
        if (!$menu) {
            return $this->redirectToRoute('app_order');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setMenu($menu);
        $commande->setNumPersons((int) $orderData['people_count']);
        $commande->setAdresseLivraison($orderData['address']);
        $commande->setVilleLivraison($orderData['city']);
        $commande->setCodePostalLivraison($orderData['zipcode']);
        $commande->setDateLivraison(new \DateTime($orderData['delivery_date']));
        $commande->setHeureLivraison(new \DateTime($orderData['delivery_time']));

        $commande->calculatePricing();

        $commande->setStatutCommande('EN_ATTENTE');
        $commande->setPretMateriel(false);

        $em->persist($commande);
        $em->flush();

        $session->remove('order_data');

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@vite-et-gourmand.fr'))
            ->to($this->getUser()->getEmail())
            ->subject('Confirmation de votre commande')
            ->htmlTemplate('emails/order_confirmation.html.twig', ['commande' => $commande]);

        $mailer->send($email);

        return $this->redirectToRoute('app_order_confirmation', ['id' => $commande->getId()]);
    }

    #[Route('/commande/confirmation/{id}', name: 'app_order_confirmation', methods: ['GET'])]
    public function confirmation(Commande $commande): Response
    {
        return $this->render('order/confirmation.html.twig', [
            'commande' => $commande
        ]);
    }
}