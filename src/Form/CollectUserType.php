<?php

namespace App\Form;

use App\Entity\Estimations;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
            'required' => true
            ])
            ->add('lastname', TextType::class, [
            'required' => true,
            ])
            ->add('address', TextType::class, [
            'required' => true
            ])
            ->add('postCode', NumberType::class, [
            'required' => true,
            'help' => '5 chiffres',
            ])
            ->add('city', TextType::class, [
            'required' => true,
            ])
            ->add('phoneNumber', TelType::class, [
            'required' => true,
            'help' => '10 chiffres',
            ])
            ->add('email', EmailType::class, [
            'required' => true,
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
