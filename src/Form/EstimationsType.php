<?php

namespace App\Form;

use App\Entity\Estimations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('estimationDate')
            ->add('isCollected')
            ->add('model')
            ->add('capacity')
            ->add('brand')
            ->add('color')
            ->add('liquidDamage')
            ->add('screenCracks')
            ->add('casingCracks')
            ->add('batteryCracks')
            ->add('buttonCracks')
            ->add('maxPrice')
            ->add('estimatedPrice')
            ->add('isValidatedPayment')
            ->add('isValidatedSignature')
            ->add('user', null, ['choice_label'=>'email'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Estimations::class,
        ]);
    }
}
