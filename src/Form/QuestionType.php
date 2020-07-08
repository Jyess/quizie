<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intitule', TextareaType::class)
            ->add('nbPointsBonneReponse', IntegerType::class, [
                'help' => "(doit être positif)",
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('nbPointsMauvaiseReponse', IntegerType::class, [
                'help' => "(doit être négatif)",
                'attr' => [
                    'max' => 0
                ]
            ])
            ->add('reponses', CollectionType::class, [
                'entry_type' => ReponseType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'label' => ' '
            ])
            ->add('quiz', HiddenType::class, [
                'mapped' => false
            ])
            ->add('valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
