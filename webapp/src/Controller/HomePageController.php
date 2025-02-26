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
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
            $pagination = $this->psd->processData($page, $searchFormData);
        }else{
            $pagination = $this->sis->showItemsPaginated($page);
        }

        return $this->render('home_page/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'items' => $pagination['items'],
            'totalPages' => $pagination['totalPages'],
            'currentPage' => $pagination['currentPage'],
        ]);


    }
}
