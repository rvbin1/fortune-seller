<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\RecipeIngredients;
use App\Entity\Recipes;
use App\Entity\Item;

class RecipeIngredientsTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $recipeIngredient = new RecipeIngredients();
        $recipe = new Recipes();
        $item = new Item();

        $recipeIngredient->setRecipe($recipe);
        $this->assertSame($recipe, $recipeIngredient->getRecipe());

        $recipeIngredient->setIngredient($item);
        $this->assertSame($item, $recipeIngredient->getIngredient());

        $recipeIngredient->setQuantity(3);
        $this->assertEquals(3, $recipeIngredient->getQuantity());
    }
}
