<?php

namespace App\Form;

use App\Entity\Organisms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganismsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organismName' , TextType::class)
            ->add('description' , TextType::class)
            ->add('organismLink' , TextType::class)
            ->add('logo', TextType::class)
            ->add('organismAddress', TextType::class)
            ->add('organismCity', TextType::class)
            ->add('organismPostcode', TextType::class)
            ->add('organismPhone', TextType::class)
            ->add('organismStatus', TextType::class)
            ->add('createNew', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisms::class,
        ]);
    }
}
