<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\MenuCondition;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
use App\Repository\RegimeRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class MenuAdminController extends AbstractController
{
    #[Route('/menus', name: 'admin_menus_list', methods: ['GET'])]
    public function menusList(
        MenuRepository $menuRepo,
        ThemeRepository $themeRepo,
        RegimeRepository $regimeRepo,
        PlatRepository $platRepo
    ): Response {
        return $this->render('dashboard_admin/menu/index.html.twig', [
            'menus' => $menuRepo->findAll(),
            'themes' => $themeRepo->findAll(),
            'regimes' => $regimeRepo->findAll(),
            'platsEntrees' => $platRepo->findBy(['type' => 'entree']),
            'platsPlats' => $platRepo->findBy(['type' => 'plat']),
            'platsDesserts' => $platRepo->findBy(['type' => 'dessert']),
        ]);
    }

    #[Route('/menus/creer', name: 'admin_menus_create', methods: ['POST'])]
    public function menuCreate(
        Request $request,
        EntityManagerInterface $em,
        ThemeRepository $themeRepo,
        RegimeRepository $regimeRepo,
        PlatRepository $platRepo
    ): Response {
        if (!$this->isCsrfTokenValid('menu_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_menus_list');
        }
        $menu = new Menu();
        $this->hydrateMenu($menu, $request, $em, $themeRepo, $regimeRepo, $platRepo);
        $em->persist($menu);
        $em->flush();
        return $this->redirectToRoute('admin_menus_list');
    }

    #[Route('/menus/{id}/modifier', name: 'admin_menus_edit', methods: ['POST'])]
    public function menuEdit(
        Menu $menu,
        Request $request,
        EntityManagerInterface $em,
        ThemeRepository $themeRepo,
        RegimeRepository $regimeRepo,
        PlatRepository $platRepo
    ): Response {
        if (!$this->isCsrfTokenValid('menu_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_menus_list');
        }
        $this->hydrateMenu($menu, $request, $em, $themeRepo, $regimeRepo, $platRepo);
        $menu->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();
        return $this->redirectToRoute('admin_menus_list');
    }

    #[Route('/menus/{id}/supprimer', name: 'admin_menus_delete', methods: ['POST'])]
    public function menuDelete(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('menu_delete_' . $menu->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_menus_list');
        }
        $em->remove($menu);
        $em->flush();
        return $this->redirectToRoute('admin_menus_list');
    }

    private function hydrateMenu(
        Menu $menu,
        Request $request,
        EntityManagerInterface $em,
        ThemeRepository $themeRepo,
        RegimeRepository $regimeRepo,
        PlatRepository $platRepo
    ): void {
        $menu->setTitre($request->request->get('titre', ''));
        $menu->setDescription($request->request->get('description', ''));
        $menu->setMinPersons((int) $request->request->get('minPersons', 1));
        $menu->setBasePrice($request->request->get('basePrice', '0'));
        $menu->setStock((int) $request->request->get('stock', 0));

        $theme = $themeRepo->find((int) $request->request->get('themeId'));
        if ($theme)
            $menu->setTheme($theme);

        $regimeId = $request->request->get('regimeId');
        $menu->setRegime($regimeId ? $regimeRepo->find((int) $regimeId) : null);

        foreach ($menu->getPlats() as $p)
            $menu->removePlat($p);
        foreach (['platEntreeId', 'platPlatId', 'platDessertId'] as $field) {
            $id = (int) $request->request->get($field);
            if ($id && $plat = $platRepo->find($id)) {
                $menu->addPlat($plat);
            }
        }

        $couverture = null;
        foreach ($menu->getPlats() as $plat) {
            if ($plat->getImages()->count() > 0) {
                $couverture = $plat->getImages()->first();
                break;
            }
        }
        if ($couverture)
            $menu->setCouverture($couverture);

        foreach ($menu->getConditions() as $cond) {
            $em->remove($cond);
        }
        $conditions = $request->request->all('conditions');
        foreach ($conditions as $texte) {
            $texte = trim($texte);
            if ($texte === '')
                continue;
            $cond = new MenuCondition();
            $cond->setContenu($texte);
            $cond->setMenu($menu);
            $em->persist($cond);
        }
    }
}