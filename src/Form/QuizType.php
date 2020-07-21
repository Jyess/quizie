<?php

namespace App\Form;

use App\Entity\Quiz;
use DateTime;
use PhpParser\Node\Stmt\Expression;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intitule')
            ->add('etat', ChoiceType::class, [
                'label' => 'Etat du quiz',
                'choices' => [
                    'Public' => '0',
                    'Privé' => '1'
                ],
                'help' => "Un quiz public sera accessible par tous, un quiz privé sera protégé par un mot de passe.",
                'mapped' => false,
                'attr' => [
                    'class' => 'custom-select'
                ]
            ])
            ->add('plageHoraireDebut', DateTimeType::class, [
                'required' => false,
                // 'widget' => 'single_text',
                // 'html5' => false,
                'attr' => [
                    'data-target' => '#datetimepicker1'
                ]
            ])
            ->add('plageHoraireFin', DateTimeType::class, [
                'required' => false,
                // 'widget' => 'single_text',
                // 'html5' => false,
                'attr' => [
                    'data-target' => '#datetimepicker2'
                ],
                'help' => "Vous pouvez définir une plage horaire pour laquelle votre quiz sera disponible."
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Je crée les questions'
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class
        ]);
    }
}
