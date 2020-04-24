<?php

namespace App\Form;

use App\Entity\Collects;
use App\Entity\Organisms;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCollect', DateTimeType::class, [
                'label' => 'Date et heure de collecte',
                'date_widget' => 'single_text',
            ])
            ->add(
                'collector',
                EntityType::class,
                [
                    'class' => Organisms::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('o')
                            ->where('o.organismStatus = \'Collecteur privé\'')
                            ->orWhere('o.organismStatus = \'Collecteur public\'')
                            ->orderBy('o.organismName', 'ASC')
                            ;
                    },
                    'required' => true,
                    'choice_label' => 'organismName',
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' => 'selectpicker'],
                    'label' => 'Organisme concerné'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Collects::class,
        ]);
    }
}
