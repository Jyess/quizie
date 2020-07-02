<?php

namespace App\Entity;

use App\Repository\ReponsesResultatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReponsesResultatRepository::class)
 */
class ReponsesResultat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Resultat::class)
     */
    private $idResultat;

    /**
     * @ORM\ManyToMany(targetEntity=Reponse::class)
     */
    private $idReponse;

    public function __construct()
    {
        $this->idResultat = new ArrayCollection();
        $this->idReponse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Resultat[]
     */
    public function getIdResultat(): Collection
    {
        return $this->idResultat;
    }

    public function addIdResultat(Resultat $idResultat): self
    {
        if (!$this->idResultat->contains($idResultat)) {
            $this->idResultat[] = $idResultat;
        }

        return $this;
    }

    public function removeIdResultat(Resultat $idResultat): self
    {
        if ($this->idResultat->contains($idResultat)) {
            $this->idResultat->removeElement($idResultat);
        }

        return $this;
    }

    /**
     * @return Collection|Reponse[]
     */
    public function getIdReponse(): Collection
    {
        return $this->idReponse;
    }

    public function addIdReponse(Reponse $idReponse): self
    {
        if (!$this->idReponse->contains($idReponse)) {
            $this->idReponse[] = $idReponse;
        }

        return $this;
    }

    public function removeIdReponse(Reponse $idReponse): self
    {
        if ($this->idReponse->contains($idReponse)) {
            $this->idReponse->removeElement($idReponse);
        }

        return $this;
    }
}
