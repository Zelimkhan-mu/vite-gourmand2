<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\HoraireRepository;
use App\Repository\MenuRepository;
use App\Repository\RegimeRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menus')]
    public function index(MenuRepository $menuRepo, ThemeRepository $themeRepo, RegimeRepository $regimeRepo, HoraireRepository $horaireRepo): Response
    {
        return $this->render('menus/index.html.twig', [
            'menus' => $menuRepo->findAll(),
            'themes' => $themeRepo->findAll(),
            'regimes' => $regimeRepo->findAll(),
            'horaires' => $horaireRepo->findAll(),
        ]);
    }

    #[Route('/menu/{id}', name: 'app_menu_detail')]
    public function detail(Menu $menu, HoraireRepository $horaireRepo): Response
    {
        return $this->render('menus/detail.html.twig', [
            'menu' => $menu,
            'horaires' => $horaireRepo->findAll(),
        ]);
    }

}
