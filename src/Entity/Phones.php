<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhonesRepository")
 */
class Phones
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $model;

    /**
     * @ORM\Column(type="integer")
     */
    private $capacity;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceLiquidDamage;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceScreenCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceCasingCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceBattery;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceButtons;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceBlacklisted;

    /**
     * @ORM\Column(type="integer")
     */
    private $priceRooted;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $validityPeriod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPriceLiquidDamage(): ?int
    {
        return $this->priceLiquidDamage;
    }

    public function setPriceLiquidDamage(int $priceLiquidDamage): self
    {
        $this->priceLiquidDamage = $priceLiquidDamage;

        return $this;
    }

    public function getPriceScreenCracks(): ?int
    {
        return $this->priceScreenCracks;
    }

    public function setPriceScreenCracks(int $priceScreenCracks): self
    {
        $this->priceScreenCracks = $priceScreenCracks;

        return $this;
    }

    public function getPriceCasingCracks(): ?int
    {
        return $this->priceCasingCracks;
    }

    public function setPriceCasingCracks(int $priceCasingCracks): self
    {
        $this->priceCasingCracks = $priceCasingCracks;

        return $this;
    }

    public function getPriceBattery(): ?int
    {
        return $this->priceBattery;
    }

    public function setPriceBattery(int $priceBattery): self
    {
        $this->priceBattery = intval($priceBattery);

        return $this;
    }

    public function getPriceButtons(): ?int
    {
        return $this->priceButtons;
    }

    public function setPriceButtons(int $priceButtons): self
    {
        $this->priceButtons = intval($priceButtons);

        return $this;
    }

    public function getPriceBlacklisted(): ?int
    {
        return $this->priceBlacklisted;
    }

    public function setPriceBlacklisted(int $priceBlacklisted): self
    {
        $this->priceBlacklisted = $priceBlacklisted;

        return $this;
    }

    public function getPriceRooted(): ?int
    {
        return $this->priceRooted;
    }

    public function setPriceRooted(int $priceRooted): self
    {
        $this->priceRooted = $priceRooted;

        return $this;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(int $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function getValidityPeriod(): ?int
    {
        return $this->validityPeriod;
    }

    public function setValidityPeriod(int $validityPeriod): self
    {
        $this->validityPeriod = $validityPeriod;

        return $this;
    }
}
