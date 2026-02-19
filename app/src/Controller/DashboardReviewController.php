<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardReviewController extends AbstractController
{
    #[Route('/mes-avis', name: 'app_reviews', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $reviews = $reviewRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('dashboard_user/review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }
}
