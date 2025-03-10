<?php
// tests/Controller/DetailPageControllerTest.php
namespace App\Tests\Controller;

use App\Controller\DetailPageController;
use App\Service\GetItemDataService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DetailPageControllerTest extends TestCase
{
    /** @var DetailPageController */
    private $controller;

    protected function setUp(): void
    {
        // Create a mock for the GetItemDataService.
        $serviceMock = $this->createMock(GetItemDataService::class);
        $serviceMock->expects($this->any())
            ->method('getItemData')
            ->with($this->isType('int'), $this->isType('bool'), $this->isType('bool'))
            ->willReturn(['item' => 'dummy data']);

        // Create a testable controller by overriding render() with the correct signature.
        $this->controller = new class($serviceMock) extends DetailPageController {
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                // Simply return a JSON response with the parameters.
                return new Response(json_encode($parameters));
            }
        };
    }

    public function testIndexWithoutQueryParameters(): void
    {
        $request = new Request(); // no query parameters
        $response = $this->controller->index(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('dummy data', $response->getContent());
    }

    public function testIndexWithQueryParameters(): void
    {
        // Set query parameters. Note that 'true' strings become boolean true via filter_var.
        $request = new Request(['crafting' => 'true', 'mysticForge' => '1']);
        $response = $this->controller->index(2, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('dummy data', $response->getContent());
    }
}
