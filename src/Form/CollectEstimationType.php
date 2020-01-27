<?php

namespace App\Form;

use App\Entity\Estimations;
use App\Entity\Phones;
use App\Repository\PhonesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectEstimationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand', EntityType::class, [
                'label' => 'Marque',
                'class' => Phones::class,
                'choice_label' => 'brand',
                'query_builder' => function (PhonesRepository $phonesRepository) {
                    return $phonesRepository->createQueryBuilder('u')->orderBy('u.brand', 'ASC')->distinct();
                }
            ])
            ->add('model', EntityType::class, [
                'label' => 'Modèle',
                'class' => Phones::class,
                'choice_label' => 'model',

            ])
            ->add('capacity', NumberType::class, [
                'label' => 'Capacité',
                'help' => 'nombre uniquement',
            ])
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
                    'oui' => '1',
                    'non' => '0'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Estimations::class,
        ]);
    }
}
