<?php

namespace App\Controller;

use App\Document\CommandeStats;
use App\Repository\MenuRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class StatsAdminController extends AbstractController
{
    #[Route('/stats', name: 'admin_stats')]
    public function stats(Request $request, DocumentManager $dm, MenuRepository $menuRepo): Response
    {
        $menuId = $request->query->get('menu');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $collection = $dm->getDocumentCollection(CommandeStats::class);

        $match = [];
        if ($menuId) {
            $match['menuId'] = (int) $menuId;
        }
        if ($dateFrom) {
            $match['createdAt']['$gte'] = new \MongoDB\BSON\UTCDateTime((new \DateTime($dateFrom))->getTimestamp() * 1000);
        }
        if ($dateTo) {
            $match['createdAt']['$lte'] = new \MongoDB\BSON\UTCDateTime((new \DateTime($dateTo))->getTimestamp() * 1000);
        }

        $pipeline = [];
        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }
        $pipeline[] = [
            '$group' => [
                '_id' => '$menuId',
                'menuTitre' => ['$first' => '$menuTitre'],
                'count' => ['$sum' => 1],
                'totalCA' => ['$sum' => '$prixTotal'],
            ]
        ];
        $pipeline[] = ['$sort' => ['count' => -1]];

        $results = iterator_to_array($collection->aggregate($pipeline));
        $totalCA = array_sum(array_map(fn($r) => $r['totalCA'], $results));

        return $this->render('dashboard_admin/stats/index.html.twig', [
            'stats' => $results,
            'totalCA' => $totalCA,
            'menus' => $menuRepo->findAll(),
            'currentMenu' => $menuId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }    
}
