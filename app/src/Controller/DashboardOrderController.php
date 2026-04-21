<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\StatutCommandeHistorique;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Review;
use App\Repository\ReviewRepository;

class DashboardOrderController extends AbstractController
{
    #[Route('/mes-commandes', name: 'app_orders', methods: ['GET'])]
    public function history(Request $request, CommandeRepository $commandeRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $status = $request->query->get('status');
        $criteria = ['user' => $user];

        $statusMap = [
            'En attente' => ['EN_ATTENTE'],
            'En cours' => ['EN_COURS', 'EN_PREPARATION', 'EN_LIVRAISON'],
            'Livrées' => ['LIVREE', 'TERMINEE'],
            'Annulées' => ['ANNULEE']
        ];

        if ($status && isset($statusMap[$status])) {
            $criteria['statutCommande'] = $statusMap[$status];
        }

        $commandes = $commandeRepo->findBy($criteria, ['createdAt' => 'DESC']);

        return $this->render('dashboard_user/order/history.html.twig', [
            'commandes' => $commandes,
            'currentFilter' => $status ?: 'Toutes'
        ]);
    }
    
    #[Route('/mes-commandes/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Commande $commande, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();

        if (!$user || $commande->getUser() !== $user) {
            throw $this->createAccessDeniedException("Il y a une erreur");
        }

        $historique = $commande->getHistoriqueStatuts()->toArray();
        usort($historique, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        $review = $reviewRepo->findOneBy(['commande' => $commande]);

        return $this->render('dashboard_user/order/popup.html.twig', [
            'commande' => $commande,
            'historique' => $historique,
            'review' => $review
        ]);
    }

    #[Route('/mes-commandes/{id}/avis', name: 'app_order_review', methods: ['POST'])]
    public function review(Commande $commande, Request $request, EntityManagerInterface $em, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();

        if (!$user || $commande->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($commande->getStatutCommande() !== 'TERMINEE') {
            throw $this->createAccessDeniedException("Cette commande n'est pas encore terminée.");
        }

        if ($reviewRepo->findOneBy(['commande' => $commande])) {
            $this->addFlash('warning', 'Vous avez déjà donné votre avis pour cette commande.');
            return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
        }

        $rating = (int) $request->request->get('rating');
        $commentaire = trim($request->request->get('commentaire', ''));

        if ($rating < 1 || $rating > 5 || $commentaire === '') {
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
            return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
        }

        $review = new Review();
        $review->setCommande($commande);
        $review->setUser($user);
        $review->setRating($rating);
        $review->setCommentaire($commentaire);

        $em->persist($review);
        $em->flush();

        $this->addFlash('success', 'Votre avis a bien été envoyé, merci !');
        return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
    }



    #[Route('/mes-commandes/{id}/annuler', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user || $commande->getUser() !== $user) {
            throw $this->createAccessDeniedException("Cette commande ne vous appartient pas.");
        }

        if (!$this->isCsrfTokenValid('cancel' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
        }

        if ($commande->getStatutCommande() !== 'EN_ATTENTE') {
            $this->addFlash('error', 'Cette commande ne peut plus être annulée.');
            return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
        }

        $commande->setStatutCommande('ANNULEE');

        $history = new StatutCommandeHistorique();
        $history->setCommande($commande);
        $history->setStatut('ANNULEE');
        $history->setChangedBy($user);
        $history->setCreatedAt(new \DateTimeImmutable());

        $em->persist($history);
        $em->flush();

        $this->addFlash('success', 'Votre commande a bien été annulée.');

        return $this->redirectToRoute('app_order_show', ['id' => $commande->getId()]);
    }
}