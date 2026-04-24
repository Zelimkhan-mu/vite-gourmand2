<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class HoraireAdminController extends AbstractController
{
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
}