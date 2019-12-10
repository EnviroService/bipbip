<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $city;

    /**
     * @ORM\Column(type="integer")
     */
    private $phone;

    /**
     * @ORM\Column(type="date")
     */
    private $signupDate;

    /**
     * @ORM\Column(type="date")
     */
    private $signinLast;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisms", inversedBy="users")
     */
    private $organismsId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Estimations", mappedBy="usersId")
     */
    private $estimations;

    public function __construct()
    {
        $this->estimations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPostcode(): ?int
    {
        return $this->postcode;
    }

    public function setPostcode(int $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getSignupDate(): ?\DateTimeInterface
    {
        return $this->signupDate;
    }

    public function setSignupDate(\DateTimeInterface $signupDate): self
    {
        $this->signupDate = $signupDate;

        return $this;
    }

    public function getSigninLast(): ?\DateTimeInterface
    {
        return $this->signinLast;
    }

    public function setSigninLast(\DateTimeInterface $signinLast): self
    {
        $this->signinLast = $signinLast;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
            $estimation->setUsersId($this);
        }

        return $this;
    }

    public function removeEstimation(Estimations $estimation): self
    {
        if ($this->estimations->contains($estimation)) {
            $this->estimations->removeElement($estimation);
            // set the owning side to null (unless already changed)
            if ($estimation->getUsersId() === $this) {
                $estimation->setUsersId(null);
            }
        }

        return $this;
    }
}
