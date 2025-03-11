<?php

namespace App\Entity;

use App\Repository\MysticForgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MysticForgeRepository::class)]
class MysticForge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'producedMysticForges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $outputItem = null;

    #[ORM\Column]
    private ?int $gw2RecipeId = null;

    /**
     * @var Collection<int, MysticForgeIngredients>
     */
    #[ORM\OneToMany(targetEntity: MysticForgeIngredients::class, mappedBy: 'mysticForge')]
    private Collection $ingredients;

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

    public function getGw2RecipeId(): ?int
    {
        return $this->gw2RecipeId;
    }

    public function setGw2RecipeId(int $gw2RecipeId): self
    {
        $this->gw2RecipeId = $gw2RecipeId;
        return $this;
    }

    /**
     * @return Collection<int, MysticForgeIngredients>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(MysticForgeIngredients $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setMysticForge($this);
        }
        return $this;
    }

    public function removeIngredient(MysticForgeIngredients $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            if ($ingredient->getMysticForge() === $this) {
                $ingredient->setMysticForge(null);
            }
        }
        return $this;
    }
}
