<?php

namespace App\Form;

use App\Entity\Boutique;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoutiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand', TextType::class, [
                'attr' => [

                ],
                'label' => 'Marque'
            ])
            ->add('model', TextType::class, [
                'attr' => [

                ],
                'label' => 'Modéle'
            ])
            ->add('capacity', NumberType::class, [
                'attr' => [

                ],
                'label' => 'Capacité'
            ])
            ->add('couleur', TextType::class, [
                'attr' => [

                ],
                'label' => 'Couleur'
            ])
            ->add('etat', ChoiceType::class, [
                'attr' => [

                ],
                'label' => 'Etat',
                'choices' => [
                    'bon' => 'bon',
                    'excelent' => 'excelent'
                ],
                'required' => true,
                'expanded' => true
            ])
            ->add('description', TextareaType::class, [
                'attr' => [

                ],
                'label' => 'description'
            ])
            ->add('prix', NumberType::class, [
                'attr' => [

                ],
                'label' => 'Prix'
            ])
            ->add('image', FileType::class, [
                'attr' => [

                ],
                'label' => 'Image',
                'data_class' => null
            ])
            ->add('isPromo', ChoiceType::class, [
                'attr' => [

                ],
                'label' => 'Le téléphone est-il en promotion?',
                'required' => true,
                'choices' => [
                    'oui' => true,
                    'non' => false
                ],
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Boutique::class,
        ]);
    }
}
