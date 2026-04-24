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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


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

    #[Route('/menus/filter', name: 'app_menus_filter', methods: ['GET'])]
    public function filter(Request $request, MenuRepository $menuRepo): JsonResponse
    {
        $maxPrice = $request->query->get('maxPrice');
        $minPrice = $request->query->get('minPrice');
        $themeIds = $request->query->all('theme');
        $regimeIds = $request->query->all('regime');
        $minPersons = $request->query->get('minPersons');

        $menus = $menuRepo->findByFilters($maxPrice, $minPrice, $themeIds, $regimeIds, $minPersons);

        $data = array_map(function($menu) {
            return [
                'id' => $menu->getId(),
                'basePrice' => $menu->getBasePrice(),
                'titre' => $menu->getTitre(),
                'description' => $menu->getDescription(),
                'theme' => $menu->getTheme()?->getId(),
                'regime' => $menu->getRegime()?->getId(),
                'minPersons' => $menu->getMinPersons(),
                'couverture' => $menu->getCouverture()?->getImagePath(),
            ];
        }, $menus);

        return new JsonResponse($data);
    }

}
