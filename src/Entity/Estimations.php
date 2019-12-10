<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EstimationsRepository")
 */
class Estimations
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
    private $estimationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $collected;

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
    private $make;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     */
    private $liquidDamage;

    /**
     * @ORM\Column(type="integer")
     */
    private $screenCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $casingCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $batteryCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $buttonCracks;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $estimatedPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validatedPayment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validatedSignature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="estimations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usersId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstimationDate(): ?string
    {
        return $this->estimationDate;
    }

    public function setEstimationDate(string $estimationDate): self
    {
        $this->estimationDate = $estimationDate;

        return $this;
    }

    public function getCollected(): ?bool
    {
        return $this->collected;
    }

    public function setCollected(bool $collected): self
    {
        $this->collected = $collected;

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

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(string $make): self
    {
        $this->make = $make;

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

    public function getLiquidDamage(): ?int
    {
        return $this->liquidDamage;
    }

    public function setLiquidDamage(int $liquidDamage): self
    {
        $this->liquidDamage = $liquidDamage;

        return $this;
    }

    public function getScreenCracks(): ?int
    {
        return $this->screenCracks;
    }

    public function setScreenCracks(int $screenCracks): self
    {
        $this->screenCracks = $screenCracks;

        return $this;
    }

    public function getCasingCracks(): ?int
    {
        return $this->casingCracks;
    }

    public function setCasingCracks(int $casingCracks): self
    {
        $this->casingCracks = $casingCracks;

        return $this;
    }

    public function getBatteryCracks(): ?int
    {
        return $this->batteryCracks;
    }

    public function setBatteryCracks(int $batteryCracks): self
    {
        $this->batteryCracks = $batteryCracks;

        return $this;
    }

    public function getButtonCracks(): ?int
    {
        return $this->buttonCracks;
    }

    public function setButtonCracks(int $buttonCracks): self
    {
        $this->buttonCracks = $buttonCracks;

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

    public function getEstimatedPrice(): ?int
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(int $estimatedPrice): self
    {
        $this->estimatedPrice = $estimatedPrice;

        return $this;
    }

    public function getValidatedPayment(): ?bool
    {
        return $this->validatedPayment;
    }

    public function setValidatedPayment(bool $validatedPayment): self
    {
        $this->validatedPayment = $validatedPayment;

        return $this;
    }

    public function getValidatedSignature(): ?bool
    {
        return $this->validatedSignature;
    }

    public function setValidatedSignature(bool $validatedSignature): self
    {
        $this->validatedSignature = $validatedSignature;

        return $this;
    }

    public function getUsersId(): ?Users
    {
        return $this->usersId;
    }

    public function setUsersId(?Users $usersId): self
    {
        $this->usersId = $usersId;

        return $this;
    }
}
