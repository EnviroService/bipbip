<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex(pattern="/^\(0\)[0-9]*$/",
     *     match=false,
     *     message="Seuls 10 chiffres sont acceptés")
     */
    private $organismPhone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Collects", mappedBy="collector")
     */
    private $collects;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $organismStatus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="organism")
     */
    private $users;

    public function __construct()
    {
        $this->collects = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getOrganismLink(): ?string
    {
        return $this->organismLink;
    }

    public function setOrganismLink(string $organismLink): self
    {
        $this->organismLink = $organismLink;

        return $this;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
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

    public function getOrganismPhone(): ?string
    {
        return $this->organismPhone;
    }

    public function setOrganismPhone(string $organismPhone): self
    {
        $this->organismPhone = $organismPhone;

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
            $collect->setCollector($this);
        }

        return $this;
    }

    public function removeCollect(Collects $collect): self
    {
        if ($this->collects->contains($collect)) {
            $this->collects->removeElement($collect);
            // set the owning side to null (unless already changed)
            if ($collect->getCollector() === $this) {
                $collect->setCollector(null);
            }
        }

        return $this;
    }

    public function getOrganismStatus(): ?string
    {
        return $this->organismStatus;
    }

    public function setOrganismStatus(string $organismStatus): self
    {
        $this->organismStatus = $organismStatus;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setOrganism($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getOrganism() === $this) {
                $user->setOrganism(null);
            }
        }

        return $this;
    }
}
