<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $collector;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="collect")
     */
    private $clients;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Estimations", mappedBy="collect")
     */
    private $estimations;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEndCollect;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->estimations = new ArrayCollection();
    }

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

    public function getCollector(): ?Organisms
    {
        return $this->collector;
    }

    public function setCollector(?Organisms $collector): self
    {
        $this->collector = $collector;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(User $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setCollect($this);
        }

        return $this;
    }

    public function removeClient(User $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
            // set the owning side to null (unless already changed)
            if ($client->getCollect() === $this) {
                $client->setCollect(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Estimations[]
     */
    public function getEstimations(): Collection
    {
        return $this->estimations;
    }

    public function addEstimation(Estimations $estimation): self
    {
        if (!$this->estimations->contains($estimation)) {
            $this->estimations[] = $estimation;
            $estimation->setCollect($this);
        }

        return $this;
    }

    public function removeEstimation(Estimations $estimation): self
    {
        if ($this->estimations->contains($estimation)) {
            $this->estimations->removeElement($estimation);
            // set the owning side to null (unless already changed)
            if ($estimation->getCollect() === $this) {
                $estimation->setCollect(null);
            }
        }

        return $this;
    }

    public function getDateEndCollect(): ?\DateTimeInterface
    {
        return $this->dateEndCollect;
    }

    public function setDateEndCollect(\DateTimeInterface $dateEndCollect): self
    {
        $this->dateEndCollect = $dateEndCollect;

        return $this;
    }
}
