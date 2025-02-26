<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\ProcessSearchData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{

    public function __construct(private readonly ProcessSearchData $psd)
    {
    }

    #[Route('/', name: 'app_home_page')]
    public function index(Request $request): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
            $this->psd->processData($searchFormData);
        }

        return $this->render('home_page/index.html.twig', [
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
