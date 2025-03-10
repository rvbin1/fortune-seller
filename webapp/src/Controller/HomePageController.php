<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\GetGemCourse;
use App\Service\ProcessSearchDataService;
use App\Service\ShowItemsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{

    public function __construct(private readonly ProcessSearchDataService $psd,
                                private readonly ShowItemsService $sis,
                                private readonly GetGemCourse $ggc)
    {
    }

    #[Route('/{page<\d+>?1}', name: 'app_home_page')]
    public function index(int $page, Request $request): Response
    {
        $query = $request->query->get('query');
        $crafting = filter_var($request->query->get('crafting', false), FILTER_VALIDATE_BOOLEAN);
        $mysticForge = filter_var($request->query->get('mysticForge', false), FILTER_VALIDATE_BOOLEAN);

        $defaultData = [
            'query' => $query,
            'crafting' => $crafting,
            'mysticForge' => $mysticForge,
        ];

        $searchForm = $this->createForm(SearchFormType::class, $defaultData);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
            return $this->redirectToRoute('app_home_page', [
                'page'  => 1,
                'query' => $searchFormData['query'],
                'crafting' => $searchFormData['crafting'],
                'mysticForge' => $searchFormData['mysticForge'],
            ]);
        }

        if ($query || $crafting || $mysticForge) {
            $searchData = array('query' => $query, 'crafting' => $crafting, 'mysticForge' => $mysticForge);
            $pagination = $this->psd->processData($page, $searchData);
        } else {
            $pagination = $this->sis->showItemsPaginated($page);
        }

        return $this->render('home_page/index.html.twig', [
            'searchForm'    => $searchForm->createView(),
            'items'         => $pagination['items'],
            'totalPages'    => $pagination['totalPages'],
            'currentPage'   => $pagination['currentPage'],
            'query'         => $query,
            'gemCourse'     => $this->ggc->getGemCourse(),
        ]);
    }
}
