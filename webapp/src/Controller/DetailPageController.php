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
        $crafting = filter_var($request->query->get('crafting', false), FILTER_VALIDATE_BOOLEAN);
        $mysticForge = filter_var($request->query->get('mysticForge', false), FILTER_VALIDATE_BOOLEAN);

        $data = $this->gids->getItemData($id, $crafting, $mysticForge);

        return $this->render('detail_page/detail_page.html.twig', $data);
    }
}
