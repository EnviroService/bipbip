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
     * @ORM\Column(type="date")
     */
    private $dateCollect;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisms", inversedBy="collects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organismsId;

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

    public function getOrganismsId(): ?Organisms
    {
        return $this->organismsId;
    }

    public function setOrganismsId(?Organisms $organismsId): self
    {
        $this->organismsId = $organismsId;

        return $this;
    }
}
