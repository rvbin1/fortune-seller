<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\MysticForgeIngredients;
use App\Entity\MysticForge;
use App\Entity\Item;

class MysticForgeIngredientsTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $ingredient = new MysticForgeIngredients();
        $mysticForge = new MysticForge();
        $item = new Item();

        $ingredient->setMysticForge($mysticForge);
        $this->assertSame($mysticForge, $ingredient->getMysticForge());

        $ingredient->setIngredientItem($item);
        $this->assertSame($item, $ingredient->getIngredientItem());

        $ingredient->setQuantity(5);
        $this->assertEquals(5, $ingredient->getQuantity());
    }
}
