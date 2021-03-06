<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 * @UniqueEntity(fields={"intitule"}, message="Session déjà existante.")
 */
class Session
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
    private $intitule;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\GreaterThanOrEqual("today", message="La session ne peut pas commencer avant aujourd'hui")
     */
    private $DateDebut;

    /**
     * @ORM\Column(type="date")
     *    @Assert\Expression(
     *      "this.getDateDebut() <= this.getDateFin()",
     *      message="La date de fin ne doit pas être antérieure à la date début"
     *      )
     */
    private $DateFin;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbPlaces;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contenir", mappedBy="session", cascade={"remove"})
     */
    private $contenir;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Stagiaire", mappedBy="sessions")
     */
    private $stagiaires;

    public function __construct()
    {
        $this->contenir = new ArrayCollection();
        $this->stagiaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->DateDebut;
    }

    public function setDateDebut(\DateTimeInterface $DateDebut): self
    {
        $this->DateDebut = $DateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->DateFin;
    }

    public function setDateFin(\DateTimeInterface $DateFin): self
    {
        $this->DateFin = $DateFin;

        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }

    public function setNbPlaces(int $nbPlaces): self
    {
        $this->nbPlaces = $nbPlaces;

        return $this;
    }

   
    /**
     * @return Collection|Contenir[]
     */
    public function getContenir(): Collection
    {
        return $this->contenir;
    }

    public function addContenir(Contenir $contenir): self
    {
        if (!$this->contenir->contains($contenir)) {
            $this->contenir[] = $contenir;
            $contenir->setSession($this);
        }

        return $this;
    }

    public function removeContenir(Contenir $contenir): self
    {
        if ($this->contenir->contains($contenir)) {
            $this->contenir->removeElement($contenir);
            // set the owning side to null (unless already changed)
            if ($contenir->getSession() === $this) {
                $contenir->setSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Stagiaire[]
     */
    public function getStagiaires(): Collection
    {
        return $this->stagiaires;
    }

    public function addStagiaire(Stagiaire $stagiaire): self
    {
        if (!$this->stagiaires->contains($stagiaire)) {
            
            if($this->nbPlaces - count($this->stagiaires) > 0){
                $this->stagiaires[] = $stagiaire;
                $stagiaire->addSession($this);
            }
        }
        return $this;
    }

    public function removeStagiaire(Stagiaire $stagiaire): self
    {
        if ($this->stagiaires->contains($stagiaire)) {
            $this->stagiaires->removeElement($stagiaire);
            $stagiaire->removeSession($this);            
        }

        return $this;
    }

    /**
     * Get the value of nbPlacesRestantes
     */ 
    public function getNbPlacesRestantes()
    {
        return $this->nbPlacesRestantes;
    }

}
