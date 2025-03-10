<?php

namespace App\Tests\Service;

use App\Entity\Item;
use App\Service\GetItemDataService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class GetItemDataServiceTest extends TestCase
{
    public function testGetItemDataReturnsCorrectStructure()
    {
        // Create a dummy Item and stub its methods to return a Collection.
        $dummyItem = $this->createMock(Item::class);
        $dummyItem->method('getUsedInRecipeIngredients')
            ->willReturn(new ArrayCollection());
        $dummyItem->method('getUsedInMysticForgeIngredients')
            ->willReturn(new ArrayCollection());

        // Create a repository mock (an instance of EntityRepository) that returns the dummy Item.
        $repositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($dummyItem);

        // Create an EntityManager mock that returns our repository mock.
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')
            ->with(Item::class)
            ->willReturn($repositoryMock);

        // Instantiate the service and call the method.
        $service = new GetItemDataService($entityManagerMock);
        $result = $service->getItemData(123, true, false);

        // Assertions.
        $this->assertArrayHasKey('item', $result);
        $this->assertSame($dummyItem, $result['item']);
        $this->assertArrayHasKey('usedRecipes', $result);
        $this->assertArrayHasKey('usedMysticRecipes', $result);
        $this->assertSame(true, $result['crafting']);
        $this->assertSame(false, $result['mysticForge']);
    }
}
