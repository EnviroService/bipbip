<?php

namespace App\Form;

use App\Entity\Organisms;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('lastname')
            ->add('firstname')
            ->add('phoneNumber')
            ->add('address')
            ->add('postCode')
            ->add('city')
            ->add(
                'organism',
                EntityType::class,
                [
                    'class' => Organisms::class,
                    'required' => false,
                    'choice_label' => 'organismName',
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' => 'selectpicker'],
                    'by_reference'=> false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
