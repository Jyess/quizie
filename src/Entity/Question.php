<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(message="Ce champs est obligatoire.")
     * @Assert\Length(max = 255, maxMessage = "Le nom du quiz doit faire moins de 255 caractères.")
     */
    private $intitule;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="La question doit avoir un nombre de points en cas de bonne réponse.")
     * @Assert\Positive(message="Le nombre de points pour une bonne réponse doit être positif.")
     */
    private $nbPointsBonneReponse;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="La question doit avoir un nombre de points en cas de mauvaise réponse.")
     * @Assert\Negative(message="Le nombre de points pour une mauvaise réponse doit être négatif.")
     */
    private $nbPointsMauvaiseReponse;

    /**
     * @ORM\ManyToOne(targetEntity=Quiz::class, inversedBy="questions",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $quiz;

    /**
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="question", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule($intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getNbPointsBonneReponse(): ?int
    {
        return $this->nbPointsBonneReponse;
    }

    public function setNbPointsBonneReponse($nbPointsBonneReponse): self
    {
        $this->nbPointsBonneReponse = $nbPointsBonneReponse;

        return $this;
    }

    public function getNbPointsMauvaiseReponse(): ?int
    {
        return $this->nbPointsMauvaiseReponse;
    }

    public function setNbPointsMauvaiseReponse($nbPointsMauvaiseReponse): self
    {
        $this->nbPointsMauvaiseReponse = $nbPointsMauvaiseReponse;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getReponses(): ?Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse)
    {
        $this->reponses->add($reponse);
        $reponse->setQuestion($this);
    }

    public function removeReponse(Reponse $reponse)
    {
        $this->reponses->removeElement($reponse);
    }

    public function __toString()
    {
        return "";
    }
}
