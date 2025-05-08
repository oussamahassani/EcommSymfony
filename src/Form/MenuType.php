<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('route', TextType::class, [
                'label' => 'Route',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'form-check'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}