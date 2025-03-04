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

    public function getItemData(int $id): array
    {
        /** @var Item $item */
        $item = $this->em->getRepository(Item::class)->find($id);

        $usedRecipes = [];
        foreach ($item->getUsedInRecipeIngredients() as $recipeIngredient) {
            $recipe = $recipeIngredient->getRecipe();
            if ($recipe->getOutputItem()->getPrice() > 0) {
                $usedRecipes[$recipe->getId()] = $recipe;
            }
        }
        $usedRecipes = array_values($usedRecipes);
        usort($usedRecipes, function($a, $b) {
            return $b->getOutputItem()->getPrice() <=> $a->getOutputItem()->getPrice();
        });

        $usedMysticRecipes = [];
        foreach ($item->getUsedInMysticForgeIngredients() as $usedIngredient) {
            $mysticForge = $usedIngredient->getMysticForge();
            if ($mysticForge->getOutputItem()->getPrice() > 0) {
                $usedMysticRecipes[$mysticForge->getId()] = $mysticForge;
            }
        }
        $usedMysticRecipes = array_values($usedMysticRecipes);
        usort($usedMysticRecipes, function($a, $b) {
            return $b->getOutputItem()->getPrice() <=> $a->getOutputItem()->getPrice();
        });

        return [
            'item'              => $item,
            'usedRecipes'       => $usedRecipes,
            'usedMysticRecipes' => $usedMysticRecipes,
        ];
    }
}
