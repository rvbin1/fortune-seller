<?php

namespace App\Tests\Service;

use App\Service\ProcessSearchDataService;
use App\Service\ShowItemsService;
use PHPUnit\Framework\TestCase;

class ProcessSearchDataServiceTest extends TestCase
{
    public function testProcessDataCallsShowItemsPaginatedWithParameters()
    {
        $expectedResult = [
            'items' => [],
            'totalPages' => 1,
            'currentPage' => 1,
        ];

        // Create a stub for ShowItemsService.
        $showItemsServiceStub = $this->createMock(ShowItemsService::class);
        $showItemsServiceStub->expects($this->once())
            ->method('showItemsPaginated')
            ->with(
                $this->equalTo(1),
                $this->equalTo('test'),
                $this->equalTo(true),
                $this->equalTo(false)
            )
            ->willReturn($expectedResult);

        $service = new ProcessSearchDataService($showItemsServiceStub);
        $result = $service->processData(1, [
            'query' => 'test',
            'crafting' => true,
            'mysticForge' => false,
        ]);

        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessDataCallsShowItemsPaginatedWithoutParameters()
    {
        $expectedResult = [
            'items' => [],
            'totalPages' => 1,
            'currentPage' => 1,
        ];

        // Create a stub for ShowItemsService.
        $showItemsServiceStub = $this->createMock(ShowItemsService::class);
        $showItemsServiceStub->expects($this->once())
            ->method('showItemsPaginated')
            ->with(1)
            ->willReturn($expectedResult);

        $service = new ProcessSearchDataService($showItemsServiceStub);
        $result = $service->processData(1, null);

        $this->assertEquals($expectedResult, $result);
    }
}
