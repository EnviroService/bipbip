<?php

namespace App\Form;

use App\Entity\Organisms;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OrganismsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organismName', TextType::class)
            ->add('description', TextareaType::class)
            ->add('organismLink', TextType::class)
            ->add('logo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'data_class' => null
            ])
            ->add('organismAddress', TextType::class)
            ->add('organismCity', TextType::class)
            ->add('organismPostcode', TextType::class)
            ->add('organismPhone', TelType::class)
            ->add('organismStatus', ChoiceType::class, [
                'choices' => [
                    'Collecteur privé' => 'Collecteur privé',
                    'Collecteur public' => 'Collecteur public',
                    'Partenaire économique' => 'Partenaire économique'
                ],
                'placeholder' => 'Type de collecteur',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organisms::class,
        ]);
    }
}
