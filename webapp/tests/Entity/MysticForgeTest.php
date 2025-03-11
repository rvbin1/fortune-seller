<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\MysticForge;
use App\Entity\Item;
use App\Entity\MysticForgeIngredients;

class MysticForgeTest extends TestCase
{
    public function testInitialValues()
    {
        $mysticForge = new MysticForge();
        $this->assertNull($mysticForge->getId());
        $this->assertEmpty($mysticForge->getIngredients());
        $this->assertNull($mysticForge->getOutputItem());
        $this->assertNull($mysticForge->getGw2RecipeId());
    }

    public function testSettersAndGetters()
    {
        $mysticForge = new MysticForge();
        $item = new Item();
        $mysticForge->setOutputItem($item);
        $this->assertSame($item, $mysticForge->getOutputItem());

        $mysticForge->setGw2RecipeId(789);
        $this->assertEquals(789, $mysticForge->getGw2RecipeId());
    }

    public function testIngredientsCollection()
    {
        $mysticForge = new MysticForge();
        $ingredient = new MysticForgeIngredients();

        $mysticForge->addIngredient($ingredient);
        $this->assertCount(1, $mysticForge->getIngredients());
        $this->assertSame($mysticForge, $ingredient->getMysticForge());

        $mysticForge->removeIngredient($ingredient);
        $this->assertCount(0, $mysticForge->getIngredients());
        $this->assertNull($ingredient->getMysticForge());
    }
}
