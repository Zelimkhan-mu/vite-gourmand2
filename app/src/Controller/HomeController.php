<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\HoraireRepository;
use App\Repository\MenuRepository;
use App\Repository\ReviewRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(HoraireRepository $horaireRepo, MenuRepository $menuRepo, ReviewRepository $reviewRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'horaires' => $horaireRepo->findAll(),
            'menus' => $menuRepo->findBy([], ['createdAt' => 'DESC'], 3),
            'reviews' => $reviewRepo->findBy(['statut' => 'validé'], ['createdAt' => 'DESC'], 3),
        ]);
    }

}