<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Entity\StatutCommandeHistorique;
use App\Entity\Review;
use App\Entity\Horaire;
use App\Entity\Plat;
use App\Entity\PlatImage;
use App\Repository\ReviewRepository;
use App\Repository\HoraireRepository;
use App\Repository\PlatRepository;
use App\Repository\AllergeneRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Menu;
use App\Entity\MenuCondition;
use App\Repository\ThemeRepository;
use App\Repository\RegimeRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_orders_list');
    }

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
    public function show(Commande $commande, Request $request, EntityManagerInterface $em, MenuRepository $menuRepo): Response
    {
        if ($request->isMethod('POST')) {
            $newStatus = $request->request->get('statut');

            $allowedStatuses = ['EN_ATTENTE', 'EN_PREPARATION', 'EN_LIVRAISON', 'LIVREE', 'TERMINEE'];

            if (in_array($newStatus, $allowedStatuses) && $newStatus !== $commande->getStatutCommande()) {
                $commande->setStatutCommande($newStatus);

                $history = new StatutCommandeHistorique();
                $history->setCommande($commande);
                $history->setStatut($newStatus);
                $history->setChangedBy($this->getUser());
                $history->setCreatedAt(new \DateTimeImmutable());

                $em->persist($history);
                $em->flush();

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

    #[Route('/horaires', name: 'admin_horaires', methods: ['GET'])]
    public function horaires(HoraireRepository $horaireRepo): Response
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $horaires = [];
        foreach ($jours as $jour) {
            $horaire = $horaireRepo->findOneBy(['jour' => $jour]);
            if (!$horaire) {
                $horaire = new Horaire();
                $horaire->setJour($jour);
            }
            $horaires[$jour] = $horaire;
        }

        return $this->render('dashboard_admin/horaire/index.html.twig', [
            'horaires' => $horaires,
        ]);
    }

    #[Route('/horaires', name: 'admin_horaires_update', methods: ['POST'])]
    public function horairesUpdate(Request $request, HoraireRepository $horaireRepo, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('horaires_update', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('admin_horaires');
        }

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        foreach ($jours as $jour) {
            $horaire = $horaireRepo->findOneBy(['jour' => $jour]);
            if (!$horaire) {
                $horaire = new Horaire();
                $horaire->setJour($jour);
                $em->persist($horaire);
            }

            $isClosed = $request->request->get('ferme_' . strtolower($jour)) === '1';
            $horaire->setClosed($isClosed);

            if (!$isClosed) {
                $ouverture = $request->request->get('ouverture_' . strtolower($jour));
                $fermeture = $request->request->get('fermeture_' . strtolower($jour));

                if ($ouverture) {
                    [$h, $m] = explode(':', $ouverture);
                    $horaire->setOuvertureHeure((int) $h);
                    $horaire->setOuvertureMinutes((int) $m);
                } else {
                    $horaire->setOuvertureHeure(null);
                    $horaire->setOuvertureMinutes(null);
                }

                if ($fermeture) {
                    [$h, $m] = explode(':', $fermeture);
                    $horaire->setFermetureHeure((int) $h);
                    $horaire->setFermetureMinutes((int) $m);
                } else {
                    $horaire->setFermetureHeure(null);
                    $horaire->setFermetureMinutes(null);
                }
            } else {
                $horaire->setOuvertureHeure(null);
                $horaire->setOuvertureMinutes(null);
                $horaire->setFermetureHeure(null);
                $horaire->setFermetureMinutes(null);
            }
        }

        $em->flush();
        $this->addFlash('success', 'Les horaires ont été mis à jour.');

        return $this->redirectToRoute('admin_horaires');
    }

    #[Route('/plats', name: 'admin_plats_list', methods: ['GET'])]
    public function platsList(PlatRepository $platRepo, AllergeneRepository $allergeneRepo, Request $request): Response
    {
        $filter = $request->query->get('type', 'tous');
        if (in_array($filter, ['entree', 'plat', 'dessert'])) {
            $plats = $platRepo->findBy(['type' => $filter], ['createdAt' => 'DESC']);
        } else {
            $plats = $platRepo->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('dashboard_admin/plat/index.html.twig', [
            'plats' => $plats,
            'currentFilter' => $filter,
            'allergenes' => $allergeneRepo->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/plats/nouveau', name: 'admin_plat_create', methods: ['POST'])]
    public function platCreate(
        Request $request,
        EntityManagerInterface $em,
        AllergeneRepository $allergeneRepo,
        SluggerInterface $slugger
    ): Response {
        if (!$this->isCsrfTokenValid('plat_create', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_plats_list');
        }

        $plat = new Plat();
        $plat->setNom($request->request->get('nom'));
        $plat->setDescription($request->request->get('description'));
        $plat->setType($request->request->get('type'));

        foreach ($request->request->all('allergenes') as $allergeneId) {
            $allergene = $allergeneRepo->find($allergeneId);
            if ($allergene) {
                $plat->addAllergene($allergene);
            }
        }

        $em->persist($plat);
        $em->flush();

        $this->handlePlatImages($request, $plat, $em, $slugger);

        $this->addFlash('success', 'Le plat a été créé avec succès.');
        return $this->redirectToRoute('admin_plats_list');
    }

    #[Route('/plats/{id}/modifier', name: 'admin_plat_edit', methods: ['POST'])]
    public function platEdit(
        Plat $plat,
        Request $request,
        EntityManagerInterface $em,
        AllergeneRepository $allergeneRepo,
        SluggerInterface $slugger
    ): Response {
        if (!$this->isCsrfTokenValid('plat_edit' . $plat->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_plats_list');
        }

        $plat->setNom($request->request->get('nom'));
        $plat->setDescription($request->request->get('description'));
        $plat->setType($request->request->get('type'));
        $plat->setUpdatedAt(new \DateTimeImmutable());

        foreach ($plat->getAllergenes() as $a) {
            $plat->removeAllergene($a);
        }
        foreach ($request->request->all('allergenes') as $allergeneId) {
            $allergene = $allergeneRepo->find($allergeneId);
            if ($allergene) {
                $plat->addAllergene($allergene);
            }
        }

        $this->handlePlatImages($request, $plat, $em, $slugger);

        $em->flush();
        $this->addFlash('success', 'Le plat a été modifié avec succès.');
        return $this->redirectToRoute('admin_plats_list');
    }

    #[Route('/plats/{id}/supprimer', name: 'admin_plat_delete', methods: ['POST'])]
    public function platDelete(Plat $plat, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('plat_delete' . $plat->getId(), $request->request->get('_token'))) {
            $em->remove($plat);
            $em->flush();
            $this->addFlash('success', 'Le plat a été supprimé.');
        }
        return $this->redirectToRoute('admin_plats_list');
    }

    private function handlePlatImages(Request $request, Plat $plat, EntityManagerInterface $em, SluggerInterface $slugger): void
    {
        $uploadedFiles = $request->files->get('images', []);
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }
        $file = $uploadedFiles[0] ?? null;
        if (!$file)
            return;

        foreach ($plat->getImages() as $oldImage) {
            $oldPath = $this->getParameter('plat_images_directory') . '/' . basename($oldImage->getImagePath());
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $em->remove($oldImage);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        try {
            $file->move(
                $this->getParameter('plat_images_directory'),
                $newFilename
            );
            $image = new PlatImage();
            $image->setImagePath('plats/' . $newFilename);
            $image->setAltText($plat->getNom());
            $image->setDisplayOrder(0);
            $image->setPlat($plat);
            $em->persist($image);
            $em->flush();
        } catch (FileException $e) {
        }
    }

    #[Route('/utilisateurs', name: 'admin_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function usersList(UserRepository $userRepo): Response
    {
        $allUsers = $userRepo->findAll();
        $users = array_filter($allUsers, function (User $user) {
            return in_array('ROLE_EMPLOYE', $user->getRoles());
        });

        return $this->render('dashboard_admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/utilisateurs/creer', name: 'admin_users_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userCreate(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        if (!$this->isCsrfTokenValid('user_create', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $email = $request->request->get('email');
        $plainPassword = $request->request->get('password');
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');

        if (!$email || !$plainPassword || !$firstName || !$lastName) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('admin_users_list');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_EMPLOYE']);

        $user->setPhone('0000000000');
        $user->setAdresse('Adresse à compléter');
        $user->setVille('Ville');
        $user->setCodePostal('00000');

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        try {
            $emailMessage = (new TemplatedEmail())
                ->from(new Address('no-reply@viteetgourmand.fr', 'Vite & Gourmand Admin'))
                ->to($user->getEmail())
                ->subject('Bienvenue chez Vite & Gourmand - Création de compte')
                ->htmlTemplate('emails/welcome_employee.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($emailMessage);
            $this->addFlash('success', 'Compte employé créé et email de bienvenue envoyé.');
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Compte créé mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/utilisateurs/{id}/toggle', name: 'admin_users_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userToggle(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('user_toggle_' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $isActive = $user->isActive() ?? true;
        $user->setActive(!$isActive);

        $em->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Le compte a été $status.");

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/utilisateurs/{id}/supprimer', name: 'admin_users_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userDelete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('user_delete_' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Compte employé supprimé.');
        return $this->redirectToRoute('admin_users_list');
    }

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