<?php
// src/Controller/DetailPageController.php
namespace App\Controller;

use App\Service\GetItemDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailPageController extends AbstractController
{
    public function __construct(private readonly GetItemDataService $gids)
    {
    }

    #[Route('/detail/{id}', name: 'app_detail_page')]
    public function index(int $id, Request $request): Response
    {
        $data = $this->gids->getItemData($id);

        return $this->render('detail_page/detail_page.html.twig', $data);
    }
}
