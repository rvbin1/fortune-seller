<?php

namespace App\Controller;

use App\Service\GetItemDataService;
use App\Service\ShowItemsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DetailPageController extends AbstractController
{
    public function __construct(private readonly GetItemDataService $gids)
    {
    }

    #[Route('/detail/{id}', name: 'app_detail_page')]
    public function index(int $id, Request $request): Response
    {
        return $this->render('detail_page/detail_page.html.twig',[
            'item' => $this->gids->getItemData($id),
        ]);
    }
}