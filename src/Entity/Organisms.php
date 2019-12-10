<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganismsRepository")
 */
class Organisms
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
    private $organismName;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $organismLink;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $organismAddress;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $organismCity;

    /**
     * @ORM\Column(type="integer")
     */
    private $organismPostcode;

    /**
     * @ORM\Column(type="integer")
     */
    private $organismPhone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Users", mappedBy="organismsId")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Collects", mappedBy="organismsId")
     */
    private $collects;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->collects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganismName(): ?string
    {
        return $this->organismName;
    }

    public function setOrganismName(string $organismName): self
    {
        $this->organismName = $organismName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getOrganismAddress(): ?string
    {
        return $this->organismAddress;
    }

    public function setOrganismAddress(string $organismAddress): self
    {
        $this->organismAddress = $organismAddress;

        return $this;
    }

    public function getOrganismCity(): ?string
    {
        return $this->organismCity;
    }

    public function setOrganismCity(string $organismCity): self
    {
        $this->organismCity = $organismCity;

        return $this;
    }

    public function getOrganismPostcode(): ?int
    {
        return $this->organismPostcode;
    }

    public function setOrganismPostcode(int $organismPostcode): self
    {
        $this->organismPostcode = $organismPostcode;

        return $this;
    }

    public function getOrganismPhone(): ?int
    {
        return $this->organismPhone;
    }

    public function setOrganismPhone(int $organismPhone): self
    {
        $this->organismPhone = $organismPhone;

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setOrganismsId($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getOrganismsId() === $this) {
                $user->setOrganismsId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Collects[]
     */
    public function getCollects(): Collection
    {
        return $this->collects;
    }

    public function addCollect(Collects $collect): self
    {
        if (!$this->collects->contains($collect)) {
            $this->collects[] = $collect;
            $collect->setOrganismsId($this);
        }

        return $this;
    }

    public function removeCollect(Collects $collect): self
    {
        if ($this->collects->contains($collect)) {
            $this->collects->removeElement($collect);
            // set the owning side to null (unless already changed)
            if ($collect->getOrganismsId() === $this) {
                $collect->setOrganismsId(null);
            }
        }

        return $this;
    }
}
