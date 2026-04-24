<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Entity\PlatImage;
use App\Repository\AllergeneRepository;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_EMPLOYE')]
class PlatAdminController extends AbstractController
{ 
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
}