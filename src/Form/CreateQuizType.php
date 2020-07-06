<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CreateQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intitule', TextType::class, [
                'label' => 'Intitulé'
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'Etat du quiz',
                'choices' => [
                    'Public' => 0,
                    'Privé' => 1
                ],
                'help' => "Un quiz public sera accessible par tous, un quiz privé sera protégé par un mot de passe."
            ])
            ->add('plageHoraireDebut', DateTimeType::class, [
                'required' => false
            ])
            ->add('plageHoraireFin', DateTimeType::class, [
                'required' => false
            ])
            ->add('cleAcces', TextType::class, [
                'required' => false,
                'constraints' => [new Length(['min' => 5, 'max' => 5, 'exactMessage' => "La clé d'accès doit contenir 5 caractères."],)]
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Je crée les questions'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
