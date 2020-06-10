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
     * @ORM\Column(type="datetime")
     */
    private $estimationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCollected;

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
    private $brand;

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
    private $isValidatedPayment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidatedCi;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="estimations")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=17)
     */
    private $imei;

    /**
     **Status selon le stade de l'estimation:
     * ** 0 => estimation effectuée.
     * ** 1 => estimation finalisée et payée.
     * ** 2 => estimation envoyée par chronopost avec étiquette.
     * ** 3 => Estimation refusée par BipBip.
     * ** 4 => estimation envoyées par chronopost avec un code recu par message.
     * ** 5 => estimation inscrit à une collecte.
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Reporting", mappedBy="estimation", cascade={"persist", "remove"})
     */
    private $reporting;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstimationDate(): ?\DateTimeInterface
    {
        return $this->estimationDate;
    }

    public function setEstimationDate(\DateTimeInterface $estimationDate): self
    {
        $this->estimationDate = $estimationDate;

        return $this;
    }

    public function getIsCollected(): ?bool
    {
        return $this->isCollected;
    }

    public function setIsCollected(bool $isCollected): self
    {
        $this->isCollected = $isCollected;

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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

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

    public function getIsValidatedPayment(): ?bool
    {
        return $this->isValidatedPayment;
    }

    public function setIsValidatedPayment(bool $isValidatedPayment): self
    {
        $this->isValidatedPayment = $isValidatedPayment;

        return $this;
    }

    public function getIsValidatedCi(): ?bool
    {
        return $this->isValidatedCi;
    }

    public function setIsValidatedCi(bool $isValidatedCi): self
    {
        $this->isValidatedCi = $isValidatedCi;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getImei(): ?string
    {
        return $this->imei;
    }

    public function setImei(string $imei): self
    {
        $this->imei = $imei;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReporting(): ?Reporting
    {
        return $this->reporting;
    }

    public function setReporting(Reporting $reporting): self
    {
        $this->reporting = $reporting;

        // set the owning side of the relation if necessary
        if ($reporting->getEstimation() !== $this) {
            $reporting->setEstimation($this);
        }

        return $this;
    }
}
