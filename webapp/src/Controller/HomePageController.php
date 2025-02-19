<?php

namespace App\Controller;

use App\Form\CheckboxFormType;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(Request $request): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        $checkBoxForm = $this->createForm(CheckboxFormType::class);
        $checkBoxForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
        }

        if ($checkBoxForm->isSubmitted() && $checkBoxForm->isValid()) {
            $checkBoxData = $checkBoxForm->getData();
        }

        return $this->render('home_page/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'checkBoxForm' => $checkBoxForm->createView(),
        ]);
    }
}
