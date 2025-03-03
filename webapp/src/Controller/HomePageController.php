<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\ProcessSearchDataService;
use App\Service\ShowItemsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{

    public function __construct(private readonly ProcessSearchDataService $psd,
                                private readonly ShowItemsService $sis)
    {
    }

    #[Route('/{page<\d+>?1}', name: 'app_home_page')]
    public function index(int $page, Request $request): Response
    {
        $query = $request->query->get('query');

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
            return $this->redirectToRoute('app_home_page', [
                'page'  => 1,
                'query' => $searchFormData['query'],
                'crafting' => $searchFormData['crafting'],
                'selling' => $searchFormData['selling'],
                'converting' => $searchFormData['converting'],
            ]);
        }

        if ($query) {
            $pagination = $this->psd->processData($page, ['query' => $query]);
        } else {
            $pagination = $this->sis->showItemsPaginated($page);
        }

        return $this->render('home_page/index.html.twig', [
            'searchForm'    => $searchForm->createView(),
            'items'         => $pagination['items'],
            'totalPages'    => $pagination['totalPages'],
            'currentPage'   => $pagination['currentPage'],
            'query'         => $query,
        ]);
    }
}
