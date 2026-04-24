<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class ReviewAdminController extends AbstractController
{
    #[Route('/avis', name: 'admin_reviews_list')]
    public function reviews(Request $request, ReviewRepository $reviewRepo): Response
    {
        $filter = $request->query->get('status', 'tous');

        if ($filter === 'en_attente') {
            $reviews = $reviewRepo->findBy(['statut' => 'en_attente'], ['createdAt' => 'DESC']);
        } elseif ($filter === 'valide') {
            $reviews = $reviewRepo->findBy(['statut' => 'validé'], ['createdAt' => 'DESC']);
        } elseif ($filter === 'rejete') {
            $reviews = $reviewRepo->findBy(['statut' => 'refusé'], ['createdAt' => 'DESC']);
        } else {
            $reviews = $reviewRepo->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('dashboard_admin/review/index.html.twig', [
            'reviews' => $reviews,
            'currentFilter' => $filter,
        ]);
    }

    #[Route('/avis/{id}/action', name: 'admin_review_action', methods: ['POST'])]
    public function reviewAction(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('review_action' . $review->getId(), $request->request->get('_token'))) {
            $action = $request->request->get('action');

            if ($action === 'valide') {
                $review->setStatut('validé');
                $this->addFlash('success', 'L\'avis a été validé.');
            } elseif ($action === 'rejete') {
                $review->setStatut('refusé');
                $this->addFlash('success', 'L\'avis a été refusé.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('admin_reviews_list');
    }
}