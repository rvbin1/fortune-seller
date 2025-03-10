<?php

namespace App\Entity;

use App\Repository\MysticForgeIngredientsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MysticForgeIngredientsRepository::class)]
class MysticForgeIngredients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MysticForge::class, inversedBy: 'ingredients')]
    private ?MysticForge $mysticForge = null;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'usedInMysticForgeIngredients')]
    private ?Item $ingredientItem = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMysticForge(): ?MysticForge
    {
        return $this->mysticForge;
    }

    public function setMysticForge(?MysticForge $mysticForge): self
    {
        $this->mysticForge = $mysticForge;
        return $this;
    }

    public function getIngredientItem(): ?Item
    {
        return $this->ingredientItem;
    }

    public function setIngredientItem(?Item $ingredientItem): self
    {
        $this->ingredientItem = $ingredientItem;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}
