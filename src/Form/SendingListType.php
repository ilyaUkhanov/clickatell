<?php

namespace App\Form;

use App\Constraints\ConstraintCSVColumn;
use App\Entity\SendingList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SendingListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('template', TextareaType::class)
            ->add('file', VichFileType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'asset_helper' => true,
                'constraints' => [
                    new ConstraintCSVColumn($column = "phone")
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SendingList::class,
        ]);
    }
}
