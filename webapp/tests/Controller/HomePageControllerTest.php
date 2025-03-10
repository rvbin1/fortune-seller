<?php
namespace App\Tests\Controller;

use App\Controller\HomePageController;
use App\Service\ProcessSearchDataService;
use App\Service\ShowItemsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomePageControllerTest extends TestCase
{
    /** @var HomePageController */
    private $controller;

    protected function setUp(): void
    {
        // Create fake implementations by extending the services.
        $fakeProcessService = new class() extends ProcessSearchDataService {
            public function processData(int $page, array|null $searchData): array
            {
                return [
                    'items'       => ['searchItem1', 'searchItem2'],
                    'totalPages'  => 2,
                    'currentPage' => 1,
                ];
            }
        };

        $fakeShowItemsService = new class() extends ShowItemsService {
            public function showItemsPaginated(int $page): array
            {
                return [
                    'items'       => ['itemA', 'itemB'],
                    'totalPages'  => 3,
                    'currentPage' => 1,
                ];
            }
        };

        // Create a testable controller that overrides createForm() and render() with the correct signature.
        $this->controller = new class($fakeProcessService, $fakeShowItemsService) extends HomePageController {
            protected function createForm($type, $data)
            {
                // A dummy form stub.
                return new class($data) {
                    private $data;
                    public function __construct($data)
                    {
                        $this->data = $data;
                    }
                    public function handleRequest($request) {}
                    public function isSubmitted(): bool { return false; }
                    public function isValid(): bool { return false; }
                    public function createView(): array { return []; }
                    public function getData(): array { return $this->data; }
                };
            }
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                // Return a JSON response for easy assertions.
                return new Response(json_encode($parameters));
            }
        };
    }

    public function testIndexWithoutSearchQuery(): void
    {
        $request = new Request();
        $response = $this->controller->index(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['itemA', 'itemB'], $content['items']);
    }

    public function testIndexWithSearchQuery(): void
    {
        $queryParams = [
            'query'       => 'test',
            'crafting'    => '1',
            'mysticForge' => 'false',
        ];
        $request = new Request($queryParams);
        $response = $this->controller->index(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['searchItem1', 'searchItem2'], $content['items']);
    }
}
