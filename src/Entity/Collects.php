<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CollectsRepository")
 */
class Collects
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
    private $dateCollect;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisms", inversedBy="collects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisms;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCollect(): ?\DateTimeInterface
    {
        return $this->dateCollect;
    }

    public function setDateCollect(\DateTimeInterface $dateCollect): self
    {
        $this->dateCollect = $dateCollect;

        return $this;
    }

    public function getOrganisms(): ?Organisms
    {
        return $this->organisms;
    }

    public function setOrganisms(?Organisms $organisms): self
    {
        $this->organisms = $organisms;

        return $this;
    }


}
