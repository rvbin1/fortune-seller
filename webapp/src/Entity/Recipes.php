<?php

namespace App\Entity;

use App\Repository\RecipesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipesRepository::class)]
class Recipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $outputItem = null;

    #[ORM\OneToMany(targetEntity: RecipeIngredients::class, mappedBy: 'recipe', cascade: ['persist', 'remove'])]
    private Collection $ingredients;

    #[ORM\Column]
    private ?int $gw2_recipe_id = null;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOutputItem(): ?Item
    {
        return $this->outputItem;
    }

    public function setOutputItem(?Item $outputItem): self
    {
        $this->outputItem = $outputItem;
        return $this;
    }

    /**
     * @return Collection<int, RecipeIngredients>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(RecipeIngredients $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setRecipe($this);
        }
        return $this;
    }

    public function removeIngredient(RecipeIngredients $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // Setze die Beziehung auf null, falls notwendig
            if ($ingredient->getRecipe() === $this) {
                $ingredient->setRecipe(null);
            }
        }
        return $this;
    }

    public function getGw2RecipeId(): ?int
    {
        return $this->gw2_recipe_id;
    }

    public function setGw2RecipeId(int $gw2_recipe_id): static
    {
        $this->gw2_recipe_id = $gw2_recipe_id;

        return $this;
    }
}
