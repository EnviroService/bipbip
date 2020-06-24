<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportingRepository")
 */
class Reporting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reportype;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datereport;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Estimations", inversedBy="reporting", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $estimation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReportype(): ?string
    {
        return $this->reportype;
    }

    public function setReportype(string $reportype): self
    {
        $this->reportype = $reportype;

        return $this;
    }

    public function getDatereport(): ?\DateTimeInterface
    {
        return $this->datereport;
    }

    public function setDatereport(\DateTimeInterface $datereport): self
    {
        $this->datereport = $datereport;

        return $this;
    }

    public function getEstimation(): ?Estimations
    {
        return $this->estimation;
    }

    public function setEstimation(Estimations $estimation): self
    {
        $this->estimation = $estimation;

        return $this;
    }
}
