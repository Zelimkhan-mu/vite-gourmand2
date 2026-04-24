<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CommandeService;
use App\Service\DistanceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MailerService;

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
    public function recap(Request $request, MenuRepository $menuRepo, DistanceService $distanceService): Response
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
        $distance = $distanceService->getDistanceFromBordeaux(
                $orderData['address'],
                $orderData['city'],
                $orderData['zipcode']
            );
        $tempCommande->setDistanceLivraisonKm($distance !== null ? (string) $distance : null);
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
    public function create(Request $request, MenuRepository $menuRepo, MailerService $mailerService, CommandeService $commandeService): Response
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

        if ($menu->getStock() <= 0) {
            $this->addFlash('error', 'Désolé, ce menu est actuellement en rupture de stock.');
            return $this->redirectToRoute('app_order');
        }

        $commande = $commandeService->createFromOrderData($orderData, $menu, $this->getUser());

        $session->remove('order_data');

        $mailerService->sendOrderConfirmation($commande);

        return $this->redirectToRoute('app_order_confirmation', ['id' => $commande->getId()]);
    }

    #[Route('/commande/confirmation/{id}', name: 'app_order_confirmation', methods: ['GET'])]
    public function confirmation(Commande $commande): Response
    {
        if ($this->getUser() !== $commande->getUser()) {
            throw $this->createAccessDeniedException("Cette commande ne vous appartient pas.");
        }

        return $this->render('order/confirmation.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/commande/frais-livraison', name: 'app_order_delivery_fee', methods: ['GET'])]
    public function deliveryFee(Request $request, DistanceService $distanceService): JsonResponse
    {
        $address = $request->query->get('address', '');
        $city = $request->query->get('city', '');
        $zipcode = $request->query->get('zipcode', '');

        if (!$address || !$city || !$zipcode) {
            return new JsonResponse(['fee' => 5.00]);
        }

        $distance = $distanceService->getDistanceFromBordeaux($address, $city, $zipcode);
        $fee = $distance !== null ? round(5.00 + ($distance * 0.59), 2) : 5.00;

        return new JsonResponse(['fee' => $fee]);
    }

}