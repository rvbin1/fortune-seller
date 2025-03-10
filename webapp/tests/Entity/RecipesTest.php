<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Recipes;
use App\Entity\Item;
use App\Entity\RecipeIngredients;

class RecipesTest extends TestCase
{
    public function testInitialValues()
    {
        $recipe = new Recipes();
        $this->assertNull($recipe->getId());
        $this->assertEmpty($recipe->getIngredients());
        $this->assertNull($recipe->getOutputItem());
        $this->assertNull($recipe->getGw2RecipeId());
    }

    public function testSettersAndGetters()
    {
        $recipe = new Recipes();
        $item = new Item();

        $recipe->setOutputItem($item);
        $this->assertSame($item, $recipe->getOutputItem());

        $recipe->setGw2RecipeId(456);
        $this->assertEquals(456, $recipe->getGw2RecipeId());
    }

    public function testIngredientsCollection()
    {
        $recipe = new Recipes();
        $ingredient = new RecipeIngredients();

        $recipe->addIngredient($ingredient);
        $this->assertCount(1, $recipe->getIngredients());
        $this->assertSame($recipe, $ingredient->getRecipe());

        $recipe->removeIngredient($ingredient);
        $this->assertCount(0, $recipe->getIngredients());
        $this->assertNull($ingredient->getRecipe());
    }
}
