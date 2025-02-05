<?php

namespace App\Form;

use App\Entity\Campaign;
use App\Entity\State;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CampaignTwoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('template', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('state', EnumType::class, [
                'class' => State::class,
                'constraints' => [
                    new NotBlank(),
                ]
            ],)
            ->add('dateStart', DateTimeType::class)
            ->add('dateEnd', DateTimeType::class)
            ->add('timezone', TimezoneType::class, [
                'preferred_choices' => ['Europe/Paris', 'Europe/Madrid', 'Europe/London', 'Europe/Bucarest'],
                'duplicate_preferred_choices' => false,
                'intl' => true
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campaign::class,
        ]);
    }
}
