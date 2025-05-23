<?php
// tests/Controller/HomePageControllerTest.php

namespace App\Tests\Controller;

use App\Controller\HomePageController;
use App\Service\ProcessSearchDataService;
use App\Service\ShowItemsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class HomePageControllerTest extends TestCase
{
    private HomePageController $controller;

    protected function setUp(): void
    {
        $processServiceMock = $this->createMock(ProcessSearchDataService::class);
        $processServiceMock->method('processData')
            ->willReturn([
                'items'       => ['searchItem1', 'searchItem2'],
                'totalPages'  => 2,
                'currentPage' => 1,
            ]);

        $showItemsServiceMock = $this->createMock(ShowItemsService::class);
        $showItemsServiceMock->method('showItemsPaginated')
            ->willReturn([
                'items'       => ['itemA', 'itemB'],
                'totalPages'  => 3,
                'currentPage' => 1,
            ]);

        $formMock = $this->createMock(FormInterface::class);
        $formMock->method('isSubmitted')->willReturn(false);
        $formMock->method('isValid')->willReturn(false);
        $formMock->method('getData')->willReturn(null);
        // Return a proper FormView instead of an array.
        $formMock->method('createView')->willReturn(new FormView());

        $this->controller = new class($processServiceMock, $showItemsServiceMock, $formMock) extends HomePageController {
            public function __construct(
                ProcessSearchDataService $psd,
                ShowItemsService $sis,
                private FormInterface $formMock
            ) {
                parent::__construct($psd, $sis);
            }

            protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
            {
                return $this->formMock;
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                return new Response(json_encode($parameters));
            }
        };
    }

    public function testIndexWithoutSearchQuery(): void
    {
        $request = new Request();
        $response = $this->controller->index(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(['itemA', 'itemB'], $data['items']);
        $this->assertEquals(3, $data['totalPages']);
        $this->assertEquals(1, $data['currentPage']);
    }

    public function testIndexWithSearchQuery(): void
    {
        $request = new Request([
            'query'       => 'test',
            'crafting'    => '1',
            'mysticForge' => 'false',
        ]);
        $response = $this->controller->index(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(['searchItem1', 'searchItem2'], $data['items']);
        $this->assertEquals(2, $data['totalPages']);
        $this->assertEquals(1, $data['currentPage']);
    }
}
