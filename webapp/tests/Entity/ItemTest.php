<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Item;
use App\Entity\Recipes;
use App\Entity\MysticForge;
use App\Entity\MysticForgeIngredients;
use App\Entity\RecipeIngredients;

class ItemTest extends TestCase
{
    public function testInitialValues()
    {
        $item = new Item();
        $this->assertNull($item->getId());
        $this->assertSame([], $item->getAttributes());
        $this->assertNull($item->getPrice());
        $this->assertNull($item->getGw2Id());
        $this->assertNull($item->getPicUrl());
        $this->assertNull($item->getName());
        $this->assertNull($item->isSellable());
        $this->assertNull($item->isCraftable());
        $this->assertEmpty($item->getProducedRecipes());
        $this->assertEmpty($item->getProducedMysticForges());
        $this->assertEmpty($item->getUsedInMysticForgeIngredients());
        $this->assertEmpty($item->getUsedInRecipeIngredients());
    }

    public function testSettersAndGetters()
    {
        $item = new Item();
        $item->setGw2Id(123);
        $this->assertEquals(123, $item->getGw2Id());

        $item->setPicUrl('http://example.com/image.png');
        $this->assertEquals('http://example.com/image.png', $item->getPicUrl());

        $item->setName('Test Item');
        $this->assertEquals('Test Item', $item->getName());

        $item->setSellable(true);
        $this->assertTrue($item->isSellable());

        $item->setAttributes([['attribute' => 'Test']]);
        $this->assertEquals([['attribute' => 'Test']], $item->getAttributes());

        $item->setCraftable(true);
        $this->assertTrue($item->isCraftable());

        $item->setPrice(123456);
        $this->assertEquals(123456, $item->getPrice());
    }

    public function testProducedRecipesCollection()
    {
        $item = new Item();
        $recipe = new Recipes();

        $item->addProducedRecipe($recipe);
        $this->assertCount(1, $item->getProducedRecipes());
        $this->assertSame($item, $recipe->getOutputItem());

        $item->removeProducedRecipe($recipe);
        $this->assertCount(0, $item->getProducedRecipes());
        $this->assertNull($recipe->getOutputItem());
    }

    public function testProducedMysticForgesCollection()
    {
        $item = new Item();
        $mysticForge = new MysticForge();

        $item->addProducedMysticForge($mysticForge);
        $this->assertCount(1, $item->getProducedMysticForges());
        $this->assertSame($item, $mysticForge->getOutputItem());

        $item->removeProducedMysticForge($mysticForge);
        $this->assertCount(0, $item->getProducedMysticForges());
        $this->assertNull($mysticForge->getOutputItem());
    }

    public function testUsedInMysticForgeIngredientsCollection()
    {
        $item = new Item();
        $ingredient = new MysticForgeIngredients();

        $item->addUsedInMysticForgeIngredient($ingredient);
        $this->assertCount(1, $item->getUsedInMysticForgeIngredients());
        $this->assertSame($item, $ingredient->getIngredientItem());

        $item->removeUsedInMysticForgeIngredient($ingredient);
        $this->assertCount(0, $item->getUsedInMysticForgeIngredients());
        $this->assertNull($ingredient->getIngredientItem());
    }

    public function testUsedInRecipeIngredientsCollection()
    {
        $item = new Item();
        $recipeIngredient = new RecipeIngredients();

        $item->addUsedInRecipeIngredient($recipeIngredient);
        $this->assertCount(1, $item->getUsedInRecipeIngredients());

        $item->removeUsedInRecipeIngredient($recipeIngredient);
        $this->assertCount(0, $item->getUsedInRecipeIngredients());
    }

    public function testGetAttributeNames()
    {
        $item = new Item();
        $item->setAttributes([
            ['attribute' => 'Attr1'],
            ['attribute' => 'Attr2']
        ]);
        $this->assertEquals('Attr1, Attr2', $item->getAttributeNames());
    }

    public function testGetConvertedPriceNotSellable()
    {
        $item = new Item();
        $this->assertEquals('not sellable', $item->getConvertedPrice());

        $item->setPrice(100);
        $item->setSellable(false);
        $this->assertEquals('not sellable', $item->getConvertedPrice());
    }

    public function testGetConvertedPriceConversion()
    {
        $item = new Item();
        $item->setSellable(true);

        $item->setPrice(123456);
        $this->assertEquals('12 Gold, 34 Silver, 56 Copper', $item->getConvertedPrice());

        $item->setPrice(50);
        $this->assertEquals('50 Copper', $item->getConvertedPrice());
    }
}
