<?php

namespace App\Service;

use App\Document\CommandeStats;
use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DocumentManager $dm,
        private DistanceService $distanceService
    ) {}

    public function createFromOrderData(array $orderData, Menu $menu, User $user): Commande
    {
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setMenu($menu);
        $commande->setNumPersons((int) $orderData['people_count']);
        $commande->setAdresseLivraison($orderData['address']);
        $commande->setVilleLivraison($orderData['city']);
        $commande->setCodePostalLivraison($orderData['zipcode']);
        $commande->setDateLivraison(new \DateTime($orderData['delivery_date']));
        $commande->setHeureLivraison(new \DateTime($orderData['delivery_time']));

        $distance = $this->distanceService->getDistanceFromBordeaux(
            $orderData['address'],
            $orderData['city'],
            $orderData['zipcode']
        );
        $commande->setDistanceLivraisonKm($distance !== null ? (string) $distance : null);
        $commande->calculatePricing();
        $commande->setStatutCommande('EN_ATTENTE');
        $commande->setPretMateriel(false);

        $menu->setStock($menu->getStock() - 1);

        $this->em->persist($commande);
        $this->em->flush();

        $stats = new CommandeStats();
        $stats->setCommandeId($commande->getId());
        $stats->setMenuId($menu->getId());
        $stats->setMenuTitre($menu->getTitre());
        $stats->setPrixTotal((float) $commande->getPrixTotal());
        $stats->setNumPersons($commande->getNumPersons());
        $stats->setCreatedAt(new \DateTime());

        $this->dm->persist($stats);
        $this->dm->flush();

        return $commande;
    }
}
