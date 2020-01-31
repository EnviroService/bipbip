<?php

namespace App\Form;

use App\Entity\Estimations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('liquidDamage', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'oui' => '1',
                    'non' => '0'
                ]
            ])
            ->add('screenCracks', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'oui' => '1',
                    'non' => '0'
                ]
            ])
            ->add('casingCracks', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'oui' => '1',
                    'non' => '0'
                ]
            ])
            ->add('batteryCracks', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'oui' => '1',
                    'non' => '0'
                ]
            ])
            ->add('buttonCracks', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'oui' => '0',
                    'non' => '1'
                ]
            ])
            ->add('imei')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Estimations::class,
        ]);
    }
}
