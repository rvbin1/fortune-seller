<?php
// src/Service/GetItemDataService.php
namespace App\Service;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;

class GetItemDataService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getItemData(int $id, ?bool $craftingBool, ?bool $mysticForgeBool): array
    {
        /** @var Item $item */
        $item = $this->em->getRepository(Item::class)->find($id);
        if (!$item) {
            return ['error' => 'Item not found'];
        }

        $usedRecipes = [];
        foreach ($item->getUsedInRecipeIngredients() as $recipeIngredient) {
            $recipe = $recipeIngredient->getRecipe();
            if ($recipe && $recipe->getOutputItem() && $recipe->getOutputItem()->getPrice() > 0) {
                $usedRecipes[$recipe->getId()] = $recipe;
            }
        }
        $usedRecipes = array_values($usedRecipes);
        usort($usedRecipes, fn($a, $b) => $b->getOutputItem()->getPrice() <=> $a->getOutputItem()->getPrice());

        $usedMysticRecipes = [];
        foreach ($item->getUsedInMysticForgeIngredients() as $usedIngredient) {
            $mysticForge = $usedIngredient->getMysticForge();
            if ($mysticForge && $mysticForge->getOutputItem() && $mysticForge->getOutputItem()->getPrice() >= 0) {
                $usedMysticRecipes[$mysticForge->getId()] = $mysticForge;
            }
        }
        $usedMysticRecipes = array_values($usedMysticRecipes);
        usort($usedMysticRecipes, fn($a, $b) => $b->getOutputItem()->getPrice() <=> $a->getOutputItem()->getPrice());

        $result = ['item' => $item];

        $result['usedRecipes'] = $usedRecipes;
        $result['usedMysticRecipes'] = $usedMysticRecipes;
        $result['crafting'] = $craftingBool;
        $result['mysticForge'] = $mysticForgeBool;

        return $result;
    }
}