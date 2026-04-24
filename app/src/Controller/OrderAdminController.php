<?php

namespace App\Controller;
use App\Entity\Commande;
use App\Entity\StatutCommandeHistorique;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\MailerService;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class OrderAdminController extends AbstractController
{
    #[Route('/commandes', name: 'admin_orders_list')]
    public function orders(Request $request, CommandeRepository $commandeRepo): Response
    {
        $status = $request->query->get('status', 'Toutes');

        if ($status === 'En attente') {
            $commandes = $commandeRepo->findBy(['statutCommande' => 'EN_ATTENTE'], ['createdAt' => 'DESC']);
        } elseif ($status === 'En cours') {
            $commandes = $commandeRepo->findBy(['statutCommande' => ['EN_COURS', 'EN_PREPARATION', 'EN_LIVRAISON']], ['createdAt' => 'DESC']);
        } elseif ($status === 'Livrées') {
            $commandes = $commandeRepo->findBy(['statutCommande' => ['LIVREE', 'TERMINEE']], ['createdAt' => 'DESC']);
        } elseif ($status === 'Annulées') {
            $commandes = $commandeRepo->findBy(['statutCommande' => 'ANNULEE'], ['createdAt' => 'DESC']);
        } else {
            $commandes = $commandeRepo->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('dashboard_admin/order/index.html.twig', [
            'commandes' => $commandes,
            'currentFilter' => $status
        ]);
    }

    #[Route('/commandes/{id}', name: 'admin_order_show', methods: ['GET', 'POST'])]
    public function show(Commande $commande, Request $request, EntityManagerInterface $em, MenuRepository $menuRepo, MailerService $mailerService): Response
    {
        if ($request->isMethod('POST')) {
            $newStatus = $request->request->get('statut');

            $allowedStatuses = ['EN_ATTENTE', 'EN_PREPARATION', 'EN_LIVRAISON', 'LIVREE','ATTENTE_RETOUR_MATERIEL', 'TERMINEE'];

            if (in_array($newStatus, $allowedStatuses) && $newStatus !== $commande->getStatutCommande()) {
                $commande->setStatutCommande($newStatus);

                $history = new StatutCommandeHistorique();
                $history->setCommande($commande);
                $history->setStatut($newStatus);
                $history->setChangedBy($this->getUser());
                $history->setCreatedAt(new \DateTimeImmutable());

                $em->persist($history);
                $em->flush();

                if ($newStatus === 'ATTENTE_RETOUR_MATERIEL') {
                    try {
                        $mailerService->sendRetourMateriel($commande);
                        $this->addFlash('info', 'L\'email demandant la restitution sous 10 jours ouvrés a été envoyé au client.');
                    } catch (\Exception $e) {
                        $this->addFlash('warning', 'Le statut a été mis à jour, mais une erreur est survenue lors de l\'envoi de l\'email : ' . $e->getMessage());
                    }
                }

                if ($newStatus === 'TERMINEE') {
                    $mailerService->sendCommandeTerminee($commande);
                }

            }

            return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
        }

        $historique = $commande->getHistoriqueStatuts()->toArray();
        usort($historique, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return $this->render('dashboard_admin/order/detail.html.twig', [
            'commande' => $commande,
            'historique' => $historique,
            'menus' => $menuRepo->findAll()
        ]);
    }

    #[Route('/commandes/{id}/edit', name: 'admin_order_edit', methods: ['POST'])]
    public function edit(Commande $commande, Request $request, EntityManagerInterface $em, MenuRepository $menuRepository): Response
    {
        if ($this->isCsrfTokenValid('edit' . $commande->getId(), $request->request->get('_token'))) {
            $commande->setAdresseLivraison($request->request->get('adresse'));
            $commande->setCodePostalLivraison($request->request->get('cp'));
            $commande->setVilleLivraison($request->request->get('ville'));
            $commande->setDateLivraison(new \DateTime($request->request->get('date')));
            $commande->setHeureLivraison(new \DateTime($request->request->get('heure')));

            $menuId = $request->request->get('menu');
            $menu = $menuRepository->find($menuId);
            if ($menu) {
                $commande->setMenu($menu);
            }
            $commande->setNumPersons((int) $request->request->get('quantity'));

            $commande->calculatePricing();

            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', 'La commande a été modifiée avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
    }

    #[Route('/commandes/{id}/annuler', name: 'admin_order_cancel', methods: ['POST'])]
    public function cancel(Commande $commande, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('cancel' . $commande->getId(), $request->request->get('_token'))) {
            $contactMethod = $request->request->get('contact_method');
            $reason = $request->request->get('reason');

            if ($contactMethod && $reason) {
                $commande->setStatutCommande('ANNULEE');
                $commande->setMoyenContactAnnulation($contactMethod);
                $commande->setMotifAnnulation($reason);

                $history = new StatutCommandeHistorique();
                $history->setCommande($commande);
                $history->setStatut('ANNULEE');
                $history->setChangedBy($this->getUser());
                $history->setCreatedAt(new \DateTimeImmutable());

                $em->persist($history);
                $em->flush();

                $this->addFlash('success', 'La commande a été annulée.');
            } else {
                $this->addFlash('error', 'Veuillez remplir tous les champs (moyen de contact et motif).');
            }
        } else {
            $this->addFlash('error', 'Token invalide.');
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
    }

}